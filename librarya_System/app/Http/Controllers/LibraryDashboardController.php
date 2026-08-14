<?php

namespace App\Http\Controllers;

use App\Services\LibraryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

abstract class LibraryDashboardController extends Controller
{
    public function __construct(protected LibraryRepository $library)
    {
    }

    abstract protected function roleKey(): string;

    abstract protected function viewName(): string;

    protected function authorizeRole(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === $this->roleKey(), 403);
    }

    protected function userId(): int
    {
        return (int) Auth::id();
    }

    protected function render(string $page, array $data = []): View
    {
        $this->authorizeRole();

        return view($this->viewName(), array_merge([
            'dashboardPage' => $page,
            'libraryStats' => $this->library->stats(),
            'libraryActivity' => $this->library->recentActivities(),
            'announcements' => $this->library->announcements($this->roleKey()),
        ], $data));
    }

    protected function toBadgeStatus(string $status): string
    {
        return strtolower($status);
    }

    protected function userProfileData($user, array $extra = []): array
    {
        return array_merge([
            'Name' => $user->name,
            'ID number' => $user->login_id,
            'Email' => $user->email,
            'Role' => ucfirst($user->role),
            'Status' => ucfirst($user->status ?? 'active'),
        ], $extra);
    }

    protected function flashRedirect(string $routeName, string $message): RedirectResponse
    {
        return redirect()->route($routeName)->with('status', $message);
    }

    protected function bookLookup(string $isbn): ?object
    {
        return $this->library->bookByIsbn($isbn);
    }

    protected function publicCatalog(Request $request): Collection
    {
        return $this->library->publicBooks($request->query('search'));
    }

    protected function updateProfileData(Request $request, array $rules, callable $payload, string $routeName, string $message): RedirectResponse
    {
        $this->authorizeRole();

        $validated = $request->validate($rules);
        $user = Auth::user();

        if ($user) {
            $user->fill($payload($validated, $user));
            $user->save();
        }

        return redirect()->route($routeName)->with('status', $message);
    }

    protected function updatePasswordData(Request $request, string $routeName, string $message): RedirectResponse
    {
        $this->authorizeRole();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if ($user) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();
        }

        return redirect()->route($routeName)->with('status', $message);
    }
}


