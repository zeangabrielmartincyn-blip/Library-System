<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\Recaptcha;
use App\Services\LibraryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleLoginController extends Controller
{
    private const ROLES = ['librarian', 'instructor', 'student', 'guest'];

    public function __construct(protected LibraryRepository $library)
    {
    }

    public function store(Request $request, string $role): RedirectResponse
    {
        $role = Str::lower($role);
        abort_unless(in_array($role, self::ROLES, true), 404);

        $isGuest = $role === 'guest';
        $validated = $request->validate(array_merge([
            'remember' => ['nullable', 'boolean'],
            'g-recaptcha-response' => ['bail', new Recaptcha],
        ], $isGuest
            ? ['mobile_number' => ['required', 'string', 'max:255']]
            : [
                'identifier' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]
        ));

        if ($isGuest) {
            $mobileNumber = preg_replace('/\D+/', '', (string) $validated['mobile_number']) ?: '';

            if ($mobileNumber === '') {
                return back()
                    ->withErrors([
                        'mobile_number' => 'Please enter a valid mobile number.',
                    ])
                    ->onlyInput('mobile_number');
            }

            $user = User::updateOrCreate(
                [
                    'role' => 'guest',
                    'mobile_number' => $mobileNumber,
                ],
                [
                    'name' => 'Guest Reader',
                    'email' => 'guest.'.$mobileNumber.'@booktrack.local',
                    'login_id' => 'GST-'.$mobileNumber,
                    'status' => 'active',
                    'password' => Hash::make(Str::random(40)),
                ]
            );

            Auth::loginUsingId($user->id, $request->boolean('remember'));
            $request->session()->regenerate();

            $this->library->logActivity(Auth::id(), 'login', 'user', Auth::id(), [
                'role' => $role,
                'identifier' => $mobileNumber,
            ]);

            return redirect()->intended(route('dashboard.guest'));
        }

        $identifierValue = $validated['identifier'];
        $identifierColumns = ['login_id', 'email'];

        foreach ($identifierColumns as $identifierColumn) {
            $credentials = [
                $identifierColumn => $identifierValue,
                'password' => $validated['password'],
                'role' => $role,
                'status' => 'active',
            ];

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                $this->library->logActivity(Auth::id(), 'login', 'user', Auth::id(), [
                    'role' => $role,
                    'identifier' => $identifierValue,
                ]);

                return redirect()->intended(route("dashboard.{$role}"));
            }
        }

        $existingAccount = DB::table('users')
            ->where('role', $role)
            ->where(function ($query) use ($identifierValue, $identifierColumns) {
                $query->whereIn('login_id', [$identifierValue])
                    ->orWhereIn('email', [$identifierValue]);
            })
            ->first(['status']);

        $message = $existingAccount && $existingAccount->status !== 'active'
            ? 'This '.$role.' account is inactive. Please contact the administrator.'
            : 'These credentials do not match '.($role === 'instructor' ? 'an ' : 'a ').$role.' account.';

        return back()
            ->withErrors([
                'identifier' => $message,
            ])
            ->onlyInput('identifier');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($userId !== null) {
            $this->library->logActivity($userId, 'logout', 'user', $userId);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}


