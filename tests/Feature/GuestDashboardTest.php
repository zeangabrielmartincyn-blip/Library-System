<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_dashboard_shows_public_catalog_and_published_announcements(): void
    {
        $guest = $this->createGuest();
        $this->createBook('9780000000001', 'The Public Library', 'A. Reader', 'Reference');

        DB::table('announcements')->insert([
            [
                'title' => 'Library orientation',
                'body' => 'Orientation is available this week.',
                'audience' => 'public',
                'published_at' => now()->toDateString(),
                'status' => 'Published',
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Internal librarian note',
                'body' => 'This should not be visible to guests.',
                'audience' => 'librarian',
                'published_at' => now()->toDateString(),
                'status' => 'Published',
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($guest)->get(route('dashboard.guest'));

        $response->assertOk()
            ->assertSee('The Public Library')
            ->assertSee('Library orientation')
            ->assertDontSee('Internal librarian note')
            ->assertSee(route('guest.book-details', '9780000000001'));
    }

    public function test_guest_can_filter_catalog_and_view_details_but_cannot_use_student_actions(): void
    {
        $guest = $this->createGuest();
        $reviewer = $this->createGuest('Reviewer');
        $bookId = $this->createBook('9780000000002', 'Accessible Catalog Book', 'A. Reader', 'Fiction', 0, 3);
        $this->createBook('9780000000003', 'Another Catalog Book', 'B. Writer', 'History');

        DB::table('book_reviews')->insert([
            'user_id' => $reviewer->id,
            'book_id' => $bookId,
            'book_isbn' => '9780000000002',
            'rating' => 5,
            'review' => 'A useful public review.',
            'review_text' => 'A useful public review.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catalog = $this->actingAs($guest)->get(route('guest.catalog', [
            'search' => 'Accessible Catalog Book',
            'availability' => 'unavailable',
        ]));

        $catalog->assertOk()
            ->assertSee('Accessible Catalog Book');

        $details = $this->actingAs($guest)->get(route('guest.book-details', '9780000000002'));

        $details->assertOk()
            ->assertSee('A useful public review.')
            ->assertSee('Login to Borrow');

        $this->actingAs($guest)
            ->post(route('student.reserve', '9780000000002'), ['quantity' => 1])
            ->assertForbidden();
    }

    private function createGuest(string $name = 'Guest Reader'): User
    {
        $timestamp = now();

        $id = DB::table('users')->insertGetId([
            'name' => $name,
            'email' => Str::uuid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'login_id' => 'GST-'.Str::upper(Str::random(8)),
            'mobile_number' => null,
            'status' => 'active',
            'email_verified_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return User::query()->findOrFail($id);
    }

    private function createBook(
        string $isbn,
        string $title,
        string $author,
        string $genre,
        int $available = 2,
        int $quantity = 2,
    ): int {
        $timestamp = now();

        return DB::table('books')->insertGetId([
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'genre' => $genre,
            'year_published' => 2025,
            'quantity' => $quantity,
            'available_quantity' => $available,
            'status' => $available > 0 ? 'Available' : 'Unavailable',
            'description' => 'A public catalog description.',
            'created_by' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
