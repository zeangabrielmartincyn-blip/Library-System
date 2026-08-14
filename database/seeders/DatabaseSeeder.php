<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            ['name' => 'Library Staff', 'email' => 'librarian@booktrack.test', 'role' => 'librarian', 'login_id' => 'LIB001'],
            ['name' => 'Sample Instructor', 'email' => 'instructor@booktrack.test', 'role' => 'instructor', 'login_id' => 'INS001'],
            ['name' => 'Sample Student', 'email' => 'student@booktrack.test', 'role' => 'student', 'login_id' => 'STU001'],
            ['name' => 'Guest Reader', 'email' => 'guest@booktrack.test', 'role' => 'guest', 'login_id' => 'GST001', 'mobile_number' => '09171234567'],
        ];

        foreach ($users as $user) {
            $data = $user + ['status' => 'active', 'password' => Hash::make('password')];

            if (($data['role'] ?? null) === 'guest') {
                unset($data['mobile_number']);
            }

            User::updateOrCreate(
                ['email' => $user['email']],
                $data
            );
        }

        $librarian = User::where('role', 'librarian')->first();
        $instructor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();
        $guest = User::where('role', 'guest')->first();

        $books = [
            ['isbn' => '978-0132350884', 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'genre' => 'Programming', 'year_published' => 2008, 'quantity' => 5, 'available_quantity' => 4, 'status' => 'Available'],
            ['isbn' => '978-0321125217', 'title' => 'Domain-Driven Design', 'author' => 'Eric Evans', 'genre' => 'Software Engineering', 'year_published' => 2003, 'quantity' => 3, 'available_quantity' => 2, 'status' => 'Available'],
            ['isbn' => '978-0262033848', 'title' => 'Introduction to Algorithms', 'author' => 'Thomas H. Cormen', 'genre' => 'Computer Science', 'year_published' => 2009, 'quantity' => 4, 'available_quantity' => 3, 'status' => 'Available'],
            ['isbn' => '978-0131103627', 'title' => 'The C Programming Language', 'author' => 'Brian W. Kernighan', 'genre' => 'Programming', 'year_published' => 1988, 'quantity' => 2, 'available_quantity' => 1, 'status' => 'Available'],
            ['isbn' => '978-1449365035', 'title' => 'Designing Data-Intensive Applications', 'author' => 'Martin Kleppmann', 'genre' => 'Database', 'year_published' => 2017, 'quantity' => 2, 'available_quantity' => 0, 'status' => 'Unavailable'],
            ['isbn' => '978-0134685991', 'title' => 'Effective Java', 'author' => 'Joshua Bloch', 'genre' => 'Programming', 'year_published' => 2018, 'quantity' => 6, 'available_quantity' => 5, 'status' => 'Available'],
            ['isbn' => '978-1491950357', 'title' => 'Designing APIs', 'author' => 'Brendan Burns', 'genre' => 'Computer Science', 'year_published' => 2022, 'quantity' => 1, 'available_quantity' => 1, 'status' => 'Available'],
            ['isbn' => '978-0596007126', 'title' => 'Head First Design Patterns', 'author' => 'Eric Freeman', 'genre' => 'Software Engineering', 'year_published' => 2004, 'quantity' => 2, 'available_quantity' => 1, 'status' => 'Available'],
        ];

        foreach ($books as $book) {
            DB::table('books')->updateOrInsert(
                ['isbn' => $book['isbn']],
                $book + ['created_by' => $librarian?->id, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $bookRows = DB::table('books')->pluck('id', 'isbn');

        if ($student && isset($bookRows['978-0132350884'])) {
            DB::table('reservations')->updateOrInsert(
                ['user_id' => $student->id, 'book_id' => $bookRows['978-0132350884'], 'status' => 'Reserved'],
                ['reserved_at' => now(), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($student && isset($bookRows['978-0131103627'])) {
            DB::table('loans')->updateOrInsert(
                ['user_id' => $student->id, 'book_id' => $bookRows['978-0131103627'], 'status' => 'Borrowed'],
                ['borrowed_at' => now()->subDays(5), 'due_at' => now()->addDays(2)->toDateString(), 'processed_by' => $librarian?->id, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($student && isset($bookRows['978-0596007126'])) {
            DB::table('loans')->updateOrInsert(
                ['user_id' => $student->id, 'book_id' => $bookRows['978-0596007126'], 'status' => 'Borrowed'],
                ['borrowed_at' => now()->subDays(12), 'due_at' => now()->subDays(3)->toDateString(), 'processed_by' => $librarian?->id, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($instructor && isset($bookRows['978-0262033848'])) {
            DB::table('loans')->updateOrInsert(
                ['user_id' => $instructor->id, 'book_id' => $bookRows['978-0262033848'], 'status' => 'Borrowed'],
                ['borrowed_at' => now()->subDays(9), 'due_at' => now()->subDays(2)->toDateString(), 'processed_by' => $librarian?->id, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $announcements = [
            ['title' => 'New Arrivals', 'body' => 'Fresh programming and research books are now available at the library desk.', 'audience' => 'public', 'published_at' => now()->toDateString(), 'status' => 'Published', 'created_by' => $librarian?->id],
            ['title' => 'Borrowing Hours', 'body' => 'Borrowing and return transactions are open from 8:00 AM to 5:00 PM on weekdays.', 'audience' => 'all', 'published_at' => now()->toDateString(), 'status' => 'Published', 'created_by' => $librarian?->id],
            ['title' => 'Reserved Shelf Reminder', 'body' => 'Please claim reserved books within 2 school days to keep them active.', 'audience' => 'student', 'published_at' => now()->toDateString(), 'status' => 'Published', 'created_by' => $librarian?->id],
        ];

        foreach ($announcements as $announcement) {
            DB::table('announcements')->updateOrInsert(
                ['title' => $announcement['title']],
                $announcement + ['updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($librarian) {
            DB::table('activity_logs')->insertOrIgnore([[
                'user_id' => $librarian->id,
                'action' => 'seeded_demo_data',
                'subject_type' => 'system',
                'subject_id' => null,
                'details' => json_encode(['message' => 'Initial dashboard data seeded']),
                'created_at' => now(),
                'updated_at' => now(),
            ]]);
        }
    }
}


