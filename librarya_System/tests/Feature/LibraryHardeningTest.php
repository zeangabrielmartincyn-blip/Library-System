<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class LibraryHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_review_books_they_have_borrowed(): void
    {
        $student = $this->createAccount('student');
        $otherBook = $this->createBook('978-0000000002', 'Unborrowed Book');
        $borrowedBook = $this->createBook('978-0000000001', 'Borrowed Book');

        $this->createLoan($student->id, $borrowedBook->id);

        $blocked = $this->actingAs($student)->from(route('student.catalog'))->post(route('student.review', $otherBook->isbn), [
            'rating' => 5,
            'review' => 'Should not be saved.',
        ]);

        $blocked->assertRedirect(route('student.catalog'));
        $blocked->assertSessionHas('error', 'You can only review books you have borrowed.');
        $this->assertDatabaseMissing('book_reviews', [
            'user_id' => $student->id,
            'book_isbn' => $otherBook->isbn,
        ]);

        $allowed = $this->actingAs($student)->from(route('student.catalog'))->post(route('student.review', $borrowedBook->isbn), [
            'rating' => 4,
            'review' => 'Solid read.',
        ]);

        $allowed->assertRedirect(route('student.catalog'));
        $allowed->assertSessionHas('status', 'Your review has been saved.');
        $this->assertDatabaseHas('book_reviews', [
            'user_id' => $student->id,
            'book_id' => $borrowedBook->id,
            'book_isbn' => $borrowedBook->isbn,
            'rating' => 4,
            'review_text' => 'Solid read.',
        ]);
    }

    public function test_student_can_mark_one_notification_as_read(): void
    {
        $student = $this->createAccount('student');
        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $student->id,
            'type' => 'announcement',
            'title' => 'New notice',
            'body' => 'Please read this update.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->patch(route('student.notifications.read.one', $notificationId));

        $response->assertRedirect();
        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }

    public function test_librarian_notifications_page_does_not_auto_mark_items_read(): void
    {
        $librarian = $this->createAccount('librarian');
        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $librarian->id,
            'type' => 'fine',
            'title' => 'Fine alert',
            'body' => 'A fine needs review.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($librarian)->get(route('dashboard.librarian'));

        $response->assertOk();
        $response->assertSee('Fine alert');
        $this->assertNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));

        $markRead = $this->actingAs($librarian)->patch(route('librarian.notifications.read.one', $notificationId));
        $markRead->assertRedirect();
        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }

    public function test_published_announcement_notifies_matching_roles(): void
    {
        $librarian = $this->createAccount('librarian');
        $instructor = $this->createAccount('instructor');
        $student = $this->createAccount('student');
        $guest = $this->createAccount('guest');

        $response = $this->actingAs($librarian)->post(route('librarian.announcements.store'), [
            'title' => 'Library hours update',
            'body' => 'The library closes earlier tomorrow.',
            'audience' => 'student',
            'published_at' => now()->toDateString(),
            'status' => 'Published',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'announcement',
            'title' => 'Library hours update',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $instructor->id,
            'type' => 'announcement',
            'title' => 'Library hours update',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $guest->id,
            'type' => 'announcement',
            'title' => 'Library hours update',
        ]);
    }

    public function test_draft_announcement_only_notifies_when_published(): void
    {
        $librarian = $this->createAccount('librarian');
        $student = $this->createAccount('student');

        $draft = $this->actingAs($librarian)->post(route('librarian.announcements.store'), [
            'title' => 'Staff note',
            'body' => 'This is still a draft.',
            'audience' => 'student',
            'published_at' => now()->toDateString(),
            'status' => 'Draft',
        ]);

        $draft->assertRedirect(route('dashboard.librarian').'#announcements');
        $this->assertDatabaseCount('notifications', 0);

        $announcementId = DB::table('announcements')->where('title', 'Staff note')->value('id');

        $publish = $this->actingAs($librarian)->patch(route('librarian.announcements.update', $announcementId), [
            'title' => 'Staff note',
            'body' => 'Now published.',
            'audience' => 'student',
            'published_at' => now()->toDateString(),
            'status' => 'Published',
        ]);

        $publish->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'announcement',
            'title' => 'Staff note',
            'body' => 'Now published.',
        ]);
    }

    public function test_student_dashboard_shows_role_specific_and_public_announcements(): void
    {
        $student = $this->createAccount('student');

        DB::table('announcements')->insert([
            [
                'title' => 'Student notice',
                'body' => 'This is for students.',
                'audience' => 'student',
                'published_at' => now()->toDateString(),
                'status' => 'Published',
                'created_by' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Public notice',
                'body' => 'This is for everyone.',
                'audience' => 'public',
                'published_at' => now()->toDateString(),
                'status' => 'Published',
                'created_by' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Librarian only',
                'body' => 'Librarians only.',
                'audience' => 'librarian',
                'published_at' => now()->toDateString(),
                'status' => 'Published',
                'created_by' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($student)->get(route('dashboard.student'));

        $response->assertOk();
        $response->assertSee('Student notice');
        $response->assertSee('Public notice');
        $response->assertDontSee('Librarian only');
    }

    public function test_required_schema_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('fines', 'amount'));
        $this->assertTrue(Schema::hasColumn('fines', 'status'));
        $this->assertTrue(Schema::hasColumn('fines', 'user_id'));
        $this->assertTrue(Schema::hasColumn('book_reviews', 'book_id'));
        $this->assertTrue(Schema::hasColumn('book_reviews', 'review_text'));
        $this->assertTrue(Schema::hasColumn('notifications', 'read_at'));
        $this->assertTrue(Schema::hasColumn('activity_logs', 'description'));
    }

    public function test_librarian_can_lookup_book_metadata_from_google_books(): void
    {
        $librarian = $this->createAccount('librarian');

        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Clean Code',
                            'authors' => ['Robert C. Martin'],
                            'categories' => ['Programming', 'Software Development'],
                            'publisher' => 'Prentice Hall',
                            'publishedDate' => '2008-08-01',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($librarian)->get(route('books.lookup', '978-0132350884'));

        $response->assertOk();
        $response->assertJson([
            'isbn' => '9780132350884',
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'genre' => 'Programming',
            'publisher' => 'Prentice Hall',
            'year_published' => 2008,
        ]);
    }

    public function test_student_can_update_profile_and_password(): void
    {
        $student = $this->createAccount('student');

        $profile = $this->actingAs($student)->patch(route('student.profile.update'), [
            'name' => 'Updated Student',
            'email' => 'updated.student@example.com',
            'mobile_number' => '09998887777',
        ]);

        $profile->assertRedirect(route('student.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Student',
            'email' => 'updated.student@example.com',
            'mobile_number' => '09998887777',
        ]);

        $password = $this->actingAs($student)->patch(route('student.profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $password->assertRedirect(route('student.profile'));
        $this->assertTrue(Hash::check('new-password-123', DB::table('users')->where('id', $student->id)->value('password')));
    }

    public function test_fine_amount_uses_configured_daily_rate(): void
    {
        config()->set('library.fine_amount_per_day', 12.5);

        $student = $this->createAccount('student');
        $book = $this->createBook('978-0000000003', 'Overdue Book');

        DB::table('loans')->insert([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'borrowed_at' => now()->subDays(10),
            'due_at' => now()->subDays(4)->toDateString(),
            'returned_at' => null,
            'status' => 'Borrowed',
            'processed_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = app(\App\Services\LibraryRepository::class)->calculateAndStoreFines();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('fines', [
            'user_id' => $student->id,
            'loan_id' => DB::table('loans')->where('user_id', $student->id)->value('id'),
            'overdue_days' => 4,
            'amount' => 50.0,
            'status' => 'Unpaid',
        ]);
    }

    private function createAccount(string $role): User
    {
        $timestamp = now();
        $id = DB::table('users')->insertGetId([
            'name' => ucfirst($role).' Tester',
            'email' => Str::uuid().'@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
            'login_id' => strtoupper(substr($role, 0, 3)).'-'.Str::upper(Str::random(8)),
            'mobile_number' => null,
            'status' => 'active',
            'email_verified_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return User::query()->findOrFail($id);
    }

    private function createBook(string $isbn, string $title): object
    {
        $timestamp = now();
        $id = DB::table('books')->insertGetId([
            'isbn' => $isbn,
            'title' => $title,
            'author' => 'Test Author',
            'genre' => 'Testing',
            'year_published' => 2024,
            'quantity' => 1,
            'available_quantity' => 1,
            'status' => 'Available',
            'description' => null,
            'created_by' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return DB::table('books')->where('id', $id)->first();
    }

    private function createLoan(int $userId, int $bookId): void
    {
        $timestamp = now();
        DB::table('loans')->insert([
            'user_id' => $userId,
            'book_id' => $bookId,
            'borrowed_at' => $timestamp,
            'due_at' => $timestamp->copy()->addDays(7)->toDateString(),
            'returned_at' => null,
            'status' => 'Borrowed',
            'processed_by' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}


