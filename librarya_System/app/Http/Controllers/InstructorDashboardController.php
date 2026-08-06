<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InstructorDashboardController extends StudentDashboardController
{
    protected function roleKey(): string
    {
        return 'instructor';
    }

    protected function viewName(): string
    {
        return 'Dashboard.instructordashboard';
    }

    protected function profileData($user): array
    {
        return $this->userProfileData($user, [
            'Course' => 'Bachelor of Science in Information Technology',
            'Year level' => '3rd Year',
            'Department' => 'College of Computing Studies',
        ]);
    }

    public function dashboard(\Illuminate\Http\Request $request): View
    {
        $view = parent::dashboard($request);

        return $view;
    }

    public function profile(): View
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return $this->render('profile', [
            'profile' => $this->profileData($user),
            'profileForm' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
                'mobile_number' => $user?->mobile_number ?? '',
            ],
        ]);
    }

    public function updateProfile(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        return $this->updateProfileData(
            $request,
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'mobile_number' => ['nullable', 'string', 'max:20'],
            ],
            function (array $validated, $user): array {
                return [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'mobile_number' => $validated['mobile_number'] !== '' ? $validated['mobile_number'] : null,
                ];
            },
            'instructor.profile',
            'Profile updated successfully.'
        );
    }

    public function updatePassword(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        return $this->updatePasswordData($request, 'instructor.profile', 'Password updated successfully.');
    }
}


