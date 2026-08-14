<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_student_can_log_in(): void
    {
        $user = $this->createAccount(status: 'active');

        $response = $this->post(route('login.store', 'student'), [
            'identifier' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.student'));
        $this->assertAuthenticatedAs(User::query()->where('id', $user->id)->first());
    }

    public function test_active_instructor_can_log_in(): void
    {
        $user = $this->createAccount(status: 'active', role: 'instructor');

        $response = $this->post(route('login.store', 'instructor'), [
            'identifier' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.instructor'));
        $this->assertAuthenticatedAs(User::query()->where('id', $user->id)->first());
    }

    public function test_active_librarian_can_log_in(): void
    {
        $user = $this->createAccount(status: 'active', role: 'librarian');

        $response = $this->post(route('login.store', 'librarian'), [
            'identifier' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.librarian'));
        $this->assertAuthenticatedAs(User::query()->where('id', $user->id)->first());
    }

    public function test_login_route_is_rate_limited_after_repeated_failures(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('login.store', 'student'), [
                'identifier' => 'missing-user',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login.store', 'student'), [
            'identifier' => 'missing-user',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_instructor_cannot_log_in_with_email(): void
    {
        $user = $this->createAccount(status: 'active', role: 'instructor');

        $response = $this->post(route('login.store', 'instructor'), [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'identifier' => 'These credentials do not match an instructor account.',
        ]);
        $this->assertGuest();
    }

    public function test_librarian_cannot_log_in_with_email(): void
    {
        $user = $this->createAccount(status: 'active', role: 'librarian');

        $response = $this->post(route('login.store', 'librarian'), [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'identifier' => 'These credentials do not match a librarian account.',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_student_cannot_log_in(): void
    {
        $user = $this->createAccount(status: 'inactive');

        $response = $this->post(route('login.store', 'student'), [
            'identifier' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'identifier' => 'Your student account is pending librarian approval.',
        ]);
        $this->assertGuest();
    }

    public function test_student_registration_page_is_available(): void
    {
        $response = $this->get(route('register.student.form'));

        $response->assertOk();
        $response->assertSee('Create Student Account');
    }

    public function test_student_can_register_for_pending_approval(): void
    {
        $librarian = $this->createAccount(status: 'active', role: 'librarian');

        $response = $this->post(route('register.student'), [
            'name' => 'New Student',
            'email' => 'new.student@example.com',
            'password' => 'strong-password-123',
            'password_confirmation' => 'strong-password-123',
            'login_id' => 'STU-REGISTER',
        ]);

        $response->assertRedirect(route('login.student'));
        $this->assertDatabaseHas('users', [
            'email' => 'new.student@example.com',
            'role' => 'student',
            'status' => 'inactive',
            'login_id' => 'STU-REGISTER',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $librarian->id,
            'type' => 'account_approval',
        ]);
    }

    public function test_pending_student_cannot_log_in_until_approved(): void
    {
        $user = $this->createAccount(status: 'inactive');

        $response = $this->post(route('login.store', 'student'), [
            'identifier' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'identifier' => 'Your student account is pending librarian approval.',
        ]);
        $this->assertGuest();
    }

    public function test_student_reservations_page_renders_reserved_books(): void
    {
        $user = $this->createAccount(status: 'active');
        $bookId = DB::table('books')->insertGetId([
            'isbn' => '978-0132350884',
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'genre' => 'Programming',
            'year_published' => 2008,
            'quantity' => 2,
            'available_quantity' => 1,
            'status' => 'Available',
            'description' => null,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reservations')->insert([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'status' => 'Reserved',
            'reserved_at' => now(),
            'cancelled_at' => null,
            'picked_up_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('student.reservations'));

        $response->assertOk();
        $response->assertSee('Clean Code');
    }

    public function test_guest_can_log_in_with_mobile_number(): void
    {
        $response = $this->post(route('login.store', 'guest'), [
            'mobile_number' => '(0900) 123-4567',
        ]);

        $response->assertRedirect(route('dashboard.guest'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'role' => 'guest',
            'name' => 'Guest Reader',
            'email' => 'guest.09001234567@booktrack.local',
            'login_id' => 'GST-09001234567',
            'mobile_number' => '09001234567',
            'status' => 'active',
        ]);
    }

    public function test_guest_login_page_does_not_show_password_field(): void
    {
        $response = $this->get(route('login.guest'));

        $response->assertOk();
        $response->assertSee('Mobile Number');
        $response->assertDontSee('name="password"', false);
        $response->assertSee('Any mobile number');
    }

    public function test_librarian_can_view_recent_logins(): void
    {
        $librarian = $this->createAccount(status: 'active', role: 'librarian');

        DB::table('activity_logs')->insert([
            [
                'user_id' => $librarian->id,
                'action' => 'login',
                'subject_type' => 'user',
                'subject_id' => $librarian->id,
                'details' => json_encode([
                    'role' => 'librarian',
                    'identifier' => $librarian->login_id,
                ]),
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ],
        ]);

        $response = $this->actingAs($librarian)->get(route('dashboard.librarian'));

        $response->assertOk();
        $response->assertSee('Recent Logins');
        $response->assertSee($librarian->name);
    }

    private function createAccount(string $status, string $role = 'student', ?string $mobileNumber = null): object
    {
        $loginPrefixes = [
            'librarian' => 'LIB',
            'instructor' => 'INS',
            'student' => 'STU',
            'guest' => 'GST',
        ];

        $timestamp = now();
        $id = DB::table('users')->insertGetId([
            'name' => 'Test Student',
            'email' => Str::uuid().'@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
            'login_id' => $loginPrefixes[$role].'-'.Str::upper(Str::random(8)),
            'mobile_number' => $mobileNumber,
            'status' => $status,
            'email_verified_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return User::query()->findOrFail($id);
    }
}


