<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LibraryRepository
{
    public function books(?string $search = null, ?string $genre = null, ?string $status = null): Collection
    {
        $ratings = $this->bookRatingsLookup();

        return DB::table('books')
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $cleanSearch = trim($search);
                $term = '%'.$cleanSearch.'%';
                $tokens = array_values(array_filter(array_unique(preg_split('/\s+/', $cleanSearch) ?: []), fn ($token) => strlen($token) > 2));

                return $query->where(function ($builder) use ($term) {
                    $builder->where('isbn', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('author', 'like', $term)
                        ->orWhere('genre', 'like', $term)
                        ->orWhere('description', 'like', $term);
                })->when(! empty($tokens), function ($builder) use ($tokens) {
                    return $builder->orWhere(function ($tokenQuery) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%'.$token.'%';
                            $tokenQuery->orWhere('title', 'like', $like)
                                ->orWhere('author', 'like', $like)
                                ->orWhere('genre', 'like', $like)
                                ->orWhere('description', 'like', $like);
                        }
                    });
                });
            })
            ->when($genre !== null && trim($genre) !== '', fn ($query) => $query->where('genre', $genre))
            ->when($status !== null && trim($status) !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('title')
            ->get()
            ->map(function ($book) use ($ratings) {
                $key = $ratings['by_id'][$book->id] ?? $ratings['by_isbn'][$this->normalizeIsbn($book->isbn)] ?? null;
                $book->avg_rating = $key['avg_rating'] ?? null;
                $book->review_count = $key['review_count'] ?? 0;

                return $this->formatBook($book);
            });
    }

    /**
     * Build a rating/review-count lookup keyed by both book_id and normalized ISBN,
     * so a review matches its book whether it was linked via book_id or via book_isbn
     * (and even if the stored ISBN has different casing/spacing/dashes).
     */
    protected function bookRatingsLookup(): array
    {
        try {
            $hasBookId = Schema::hasColumn('book_reviews', 'book_id');
            $hasIsbn = Schema::hasColumn('book_reviews', 'book_isbn');

            $columns = array_values(array_filter([
                $hasBookId ? 'book_id' : null,
                $hasIsbn ? 'book_isbn' : null,
                'rating',
            ]));

            $reviews = DB::table('book_reviews')->get($columns);

            $isbnByBookId = DB::table('books')->pluck('isbn', 'id');

            $byId = [];
            $byIsbn = [];

            foreach ($reviews->groupBy(fn ($r) => $hasBookId ? ($r->book_id ?? 'unmatched') : 'unmatched') as $bookId => $group) {
                if ($bookId !== 'unmatched' && $bookId !== null) {
                    $byId[$bookId] = [
                        'avg_rating' => round((float) $group->avg('rating'), 1),
                        'review_count' => $group->count(),
                    ];
                }
            }

            // Also index by normalized ISBN so reviews that only have book_isbn set
            // (or whose book_id failed to backfill) still resolve to their book.
            $byIsbnGroups = $reviews->groupBy(function ($r) use ($isbnByBookId, $hasBookId) {
                if ($hasBookId && ! empty($r->book_id) && isset($isbnByBookId[$r->book_id])) {
                    return $this->normalizeIsbn($isbnByBookId[$r->book_id]);
                }

                return $this->normalizeIsbn($r->book_isbn ?? '');
            });

            foreach ($byIsbnGroups as $isbn => $group) {
                if ($isbn === '') {
                    continue;
                }

                $byIsbn[$isbn] = [
                    'avg_rating' => round((float) $group->avg('rating'), 1),
                    'review_count' => $group->count(),
                ];
            }

            return ['by_id' => $byId, 'by_isbn' => $byIsbn];
        } catch (\Throwable $e) {
            report($e);

            return ['by_id' => [], 'by_isbn' => []];
        }
    }

    protected function normalizeIsbn(?string $isbn): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $isbn) ?? '');
    }

    public function publicBooks(?string $search = null, ?string $genre = null): Collection
    {
        return $this->books($search, $genre);
    }

    public function genres(): Collection
    {
        return DB::table('books')->distinct()->orderBy('genre')->pluck('genre')->values();
    }

    public function bookByIsbn(string $isbn)
    {
        return DB::table('books')->where('isbn', $isbn)->first();
    }

    public function createOrUpdateBook(array $data, ?int $createdBy = null): void
    {
        $quantity = (int) ($data['quantity'] ?? 0);
        $available = (int) ($data['available_quantity'] ?? $quantity);
        $isbn = $data['isbn'];
        $exists = DB::table('books')->where('isbn', $isbn)->exists();

        $payload = [
            'title' => $data['title'],
            'author' => $data['author'],
            'genre' => $data['genre'],
            'year_published' => $data['year_published'] ?? null,
            'quantity' => $quantity,
            'available_quantity' => $available,
            'location' => $data['location'] ?? null,
            'status' => $data['status'] ?? ($available > 0 ? 'Available' : 'Unavailable'),
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('books')->where('isbn', $isbn)->update($payload);

            return;
        }

        DB::table('books')->insert(array_merge($payload, [
            'isbn' => $isbn,
            'created_by' => $createdBy,
            'created_at' => now(),
        ]));
    }

    public function updateBook(string $isbn, array $data, ?int $updatedBy = null): void
    {
        $payload = [
            'title' => $data['title'],
            'author' => $data['author'],
            'genre' => $data['genre'],
            'year_published' => $data['year_published'] ?? null,
            'quantity' => (int) $data['quantity'],
            'available_quantity' => (int) ($data['available_quantity'] ?? $data['quantity']),
            'location' => $data['location'] ?? null,
            'status' => $data['status'] ?? ((int) ($data['available_quantity'] ?? $data['quantity']) > 0 ? 'Available' : 'Unavailable'),
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ];

        DB::table('books')->where('isbn', $isbn)->update($payload);
    }

    public function deleteBook(string $isbn): void
    {
        DB::table('books')->where('isbn', $isbn)->delete();
    }

    public function studentDirectory(?string $search = null): Collection
    {
        return DB::table('users')
            ->where('role', 'student')
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $term = '%'.trim($search).'%';

                return $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', $term)
                        ->orWhere('login_id', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function instructorDirectory(?string $search = null): Collection
    {
        return DB::table('users')
            ->where('role', 'instructor')
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $term = '%'.trim($search).'%';

                return $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', $term)
                        ->orWhere('login_id', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function allUsers(?string $search = null, ?string $role = null): Collection
    {
        return DB::table('users')
            ->when($role !== null && $role !== '', fn ($query) => $query->where('role', $role))
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $term = '%'.trim($search).'%';

                return $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('login_id', 'like', $term);
                });
            })
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }

    public function createUser(array $data): int
    {
        return DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'login_id' => $data['login_id'] ?? null,
            'mobile_number' => $data['mobile_number'] ?? null,
            'status' => $data['status'] ?? 'active',
            'password' => $data['password'],
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updateUserRole(int $userId, string $role): void
    {
        DB::table('users')->where('id', $userId)->update([
            'role' => $role,
            'updated_at' => now(),
        ]);
    }

    public function toggleUserStatus(int $userId): void
    {
        $user = DB::table('users')->where('id', $userId)->first();

        if (! $user) {
            return;
        }

        DB::table('users')->where('id', $userId)->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
            'updated_at' => now(),
        ]);
    }

    public function reservationsForUser(int $userId): Collection
    {
        $query = DB::table('reservations')
            ->join('books', 'books.id', '=', 'reservations.book_id')
            ->where('reservations.user_id', $userId)
            ->where('reservations.status', 'Reserved')
            ->orderBy('reservations.reserved_at', 'desc');

        if (Schema::hasColumn('reservations', 'quantity')) {
            return $query->get([
                'reservations.quantity',
                'books.isbn',
                'books.title',
                'books.author',
                'books.genre',
                'reservations.reserved_at',
            ]);
        }

        return $query->get([
            'books.isbn',
            'books.title',
            'books.author',
            'books.genre',
            'reservations.reserved_at',
        ])->map(function ($item) {
            $item->quantity = 1;

            return $item;
        });
    }

    public function activeReservations(): Collection
    {
        $query = DB::table('reservations')
            ->join('books', 'books.id', '=', 'reservations.book_id')
            ->join('users', 'users.id', '=', 'reservations.user_id')
            ->where('reservations.status', 'Reserved')
            ->orderBy('reservations.reserved_at', 'desc');

        if (Schema::hasColumn('reservations', 'quantity')) {
            return $query->get([
                'reservations.id',
                'reservations.quantity',
                'books.isbn',
                'books.title',
                'users.name as borrower_name',
                'users.id as borrower_user_id',
                'users.login_id as borrower_id',
                'reservations.reserved_at',
            ]);
        }

        return $query->get([
            'reservations.id',
            'books.isbn',
            'books.title',
            'users.name as borrower_name',
            'users.id as borrower_user_id',
            'users.login_id as borrower_id',
            'reservations.reserved_at',
        ])->map(function ($item) {
            $item->quantity = 1;

            return $item;
        });
    }

    public function issueReservation(int $reservationId, ?int $processedBy = null): array
    {
        $reservation = DB::table('reservations')
            ->join('books', 'books.id', '=', 'reservations.book_id')
            ->join('users', 'users.id', '=', 'reservations.user_id')
            ->where('reservations.id', $reservationId)
            ->where('reservations.status', 'Reserved')
            ->first([
                'reservations.id',
                'reservations.user_id',
                'reservations.book_id',
                'books.isbn',
                'books.title',
                'books.available_quantity',
                'users.name as borrower_name',
                'users.login_id as borrower_id',
            ]);

        if (! $reservation) {
            return ['ok' => false, 'message' => 'Reservation not found.'];
        }

        $reservationQuantity = max(1, (int) ($reservation->quantity ?? 1));

        if ((int) $reservation->available_quantity < $reservationQuantity) {
            return ['ok' => false, 'message' => 'No available copies to issue.'];
        }

        DB::transaction(function () use ($reservation, $processedBy) {
            $reservationQuantity = max(1, (int) ($reservation->quantity ?? 1));
            $dueDate = $this->dueDateForUser((int) $reservation->user_id)->toDateString();

            for ($i = 0; $i < $reservationQuantity; $i++) {
                DB::table('loans')->insert([
                    'user_id' => $reservation->user_id,
                    'book_id' => $reservation->book_id,
                    'borrowed_at' => now(),
                    'due_at' => $dueDate,
                    'status' => 'Borrowed',
                    'processed_by' => $processedBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('books')->where('id', $reservation->book_id)->update([
                'available_quantity' => max(0, (int) $reservation->available_quantity - $reservationQuantity),
                'status' => ((int) $reservation->available_quantity - $reservationQuantity) > 0 ? 'Available' : 'Unavailable',
                'updated_at' => now(),
            ]);

            DB::table('reservations')->where('id', $reservation->id)->delete();
        });

        $this->logActivity($processedBy, 'issue_reservation', 'reservation', $reservation->id, [
            'isbn' => $reservation->isbn,
            'title' => $reservation->title,
            'borrower' => $reservation->borrower_id,
        ]);

        return ['ok' => true, 'message' => $reservation->title.' has been issued to '.$reservation->borrower_name.'.'];
    }

    public function declineReservation(int $reservationId, ?int $processedBy = null): array
    {
        $reservation = DB::table('reservations')
            ->join('books', 'books.id', '=', 'reservations.book_id')
            ->join('users', 'users.id', '=', 'reservations.user_id')
            ->where('reservations.id', $reservationId)
            ->where('reservations.status', 'Reserved')
            ->first([
                'reservations.id',
                'books.isbn',
                'books.title',
                'users.login_id as borrower_id',
                'users.name as borrower_name',
            ]);

        if (! $reservation) {
            return ['ok' => false, 'message' => 'Reservation not found.'];
        }

        DB::table('reservations')->where('id', $reservation->id)->update([
            'status' => 'Declined',
            'cancelled_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($processedBy, 'decline_reservation', 'reservation', $reservation->id, [
            'isbn' => $reservation->isbn,
            'title' => $reservation->title,
            'borrower' => $reservation->borrower_id,
        ]);

        return ['ok' => true, 'message' => 'Reservation declined for '.$reservation->borrower_name.'.'];
    }

    public function reserveBook(int $userId, string $isbn, int $quantity = 1): array
    {
        $quantity = max(1, min(3, $quantity));
        $supportsQuantity = Schema::hasColumn('reservations', 'quantity');
        $book = $this->bookByIsbn($isbn);

        if (! $book) {
            return ['ok' => false, 'message' => 'Book not found.'];
        }

        if ($this->hasUnpaidFines($userId)) {
            return ['ok' => false, 'message' => 'You have unpaid fines. Please settle all fines before making a reservation.'];
        }

        return DB::transaction(function () use ($userId, $isbn, $quantity, $supportsQuantity, $book) {
            $existing = DB::table('reservations')
                ->join('books', 'books.id', '=', 'reservations.book_id')
                ->where('reservations.user_id', $userId)
                ->where('books.isbn', $isbn)
                ->where('reservations.status', 'Reserved')
                ->lockForUpdate()
                ->first(['reservations.id', 'reservations.quantity']);

            if ($existing) {
                if ($supportsQuantity) {
                    DB::table('reservations')->where('id', $existing->id)->update([
                        'quantity' => (int) ($existing->quantity ?? 1) + $quantity,
                        'updated_at' => now(),
                    ]);
                }

                $this->logActivity($userId, 'reserve_book', 'book', $book->id, [
                    'isbn' => $book->isbn,
                    'title' => $book->title,
                    'quantity' => $quantity,
                ]);

                return ['ok' => true, 'message' => $quantity.' copy/copies of '.$book->title.' have been reserved.'];
            }

            $freshBook = DB::table('books')->where('id', $book->id)->lockForUpdate()->first();

            if ((int) $freshBook->available_quantity < 1) {
                return ['ok' => false, 'message' => 'No available copies right now.'];
            }

            if ((int) $freshBook->available_quantity < $quantity) {
                return ['ok' => false, 'message' => 'Only '.(int) $freshBook->available_quantity.' copy/copies are available right now.'];
            }

            DB::table('reservations')->insert([
                'user_id' => $userId,
                'book_id' => $book->id,
                'status' => 'Reserved',
                'reserved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ] + ($supportsQuantity ? ['quantity' => $quantity] : []));

            $this->logActivity($userId, 'reserve_book', 'book', $book->id, [
                'isbn' => $book->isbn,
                'title' => $book->title,
                'quantity' => $quantity,
            ]);

            return ['ok' => true, 'message' => $quantity.' copy/copies of '.$book->title.' have been reserved.'];
        });
    }

    public function cancelReservation(int $userId, string $isbn): array
    {
        $reservation = DB::table('reservations')
            ->join('books', 'books.id', '=', 'reservations.book_id')
            ->where('reservations.user_id', $userId)
            ->where('books.isbn', $isbn)
            ->where('reservations.status', 'Reserved')
            ->first(['reservations.id', 'books.title']);

        if (! $reservation) {
            return ['ok' => false, 'message' => 'Reservation not found.'];
        }

        DB::table('reservations')->where('id', $reservation->id)->delete();

        $this->logActivity($userId, 'cancel_reservation', 'reservation', $reservation->id, ['title' => $reservation->title]);

        return ['ok' => true, 'message' => 'Reservation cancelled.'];
    }

    public function issueBook(int $bookId, int $userId, ?int $processedBy = null): array
    {
        $book = DB::table('books')->where('id', $bookId)->first();
        if (! $book || (int) $book->available_quantity < 1) {
            return ['ok' => false, 'message' => 'No available copies to issue.'];
        }

        $borrowLimit = $this->borrowLimitForUser($userId);
        $activeLoans = DB::table('loans')
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->count();

        if ($activeLoans >= $borrowLimit) {
            return ['ok' => false, 'message' => "Borrow limit reached. You can only have {$borrowLimit} borrowed book(s) at a time."];
        }

        if ($this->hasUnpaidFines($userId)) {
            return ['ok' => false, 'message' => 'This borrower has unpaid fines. Please settle all fines before borrowing.'];
        }

        DB::transaction(function () use ($book, $userId, $processedBy) {
            DB::table('loans')->insert([
                'user_id' => $userId,
                'book_id' => $book->id,
                'borrowed_at' => now(),
                'due_at' => $this->dueDateForUser($userId)->toDateString(),
                'status' => 'Borrowed',
                'processed_by' => $processedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('books')->where('id', $book->id)->update([
                'available_quantity' => DB::raw('GREATEST(0, available_quantity - 1)'),
                'status' => DB::raw("CASE WHEN available_quantity - 1 > 0 THEN 'Available' ELSE 'Unavailable' END"),
                'updated_at' => now(),
            ]);
        });

        $this->logActivity($processedBy, 'issue_book', 'book', $book->id, ['user_id' => $userId]);

        return ['ok' => true, 'message' => 'Book issued successfully.'];
    }

    public function borrowLimitForUser(int $userId): int
    {
        $role = DB::table('users')->where('id', $userId)->value('role');

        return match ($role) {
            'student' => 5,
            'instructor' => 5,
            default => 0,
        };
    }

    /**
     * How long a borrower gets to keep a book before it's due.
     * Instructors get a full semester (6 months), since their materials
     * are typically tied to a course that runs the whole term.
     * Everyone else keeps the standard 7-day loan period.
     */
    public function dueDateForUser(int $userId): \Illuminate\Support\Carbon
    {
        $role = DB::table('users')->where('id', $userId)->value('role');

        return match ($role) {
            'instructor' => now()->addMonths(6),
            default => now()->addDays(7),
        };
    }

    public function activeLoanCountForUser(int $userId): int
    {
        return DB::table('loans')
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->count();
    }

    public function issueBookByIsbn(string $isbn, int $userId, ?int $processedBy = null): array
    {
        $book = $this->bookByIsbn($isbn);

        if (! $book) {
            return ['ok' => false, 'message' => 'Book not found.'];
        }

        return $this->issueBook((int) $book->id, $userId, $processedBy);
    }

    public function returnBookByLoanId(int $loanId, ?int $processedBy = null): array
    {
        $loan = DB::table('loans')->where('id', $loanId)->first();
        if (! $loan || $loan->returned_at !== null) {
            return ['ok' => false, 'message' => 'Loan record not found or already returned.'];
        }

        DB::transaction(function () use ($loan, $processedBy) {
            DB::table('loans')->where('id', $loan->id)->update([
                'returned_at' => now(),
                'status' => 'Returned',
                'updated_at' => now(),
            ]);

            $book = DB::table('books')->where('id', $loan->book_id)->first();
            if ($book) {
                $newAvailable = (int) $book->available_quantity + 1;
                $newStatus = match ($book->status) {
                    'Archived' => 'Archived',
                    default => $newAvailable > 0 ? 'Available' : 'Unavailable',
                };
                DB::table('books')->where('id', $book->id)->update([
                    'available_quantity' => $newAvailable,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
            }
        });

        $this->logActivity($processedBy, 'return_book', 'loan', $loan->id, ['book_id' => $loan->book_id]);

        return ['ok' => true, 'message' => 'Book returned successfully.'];
    }

    public function activeLoans(): Collection
    {
        return DB::table('loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->join('users', 'users.id', '=', 'loans.user_id')
            ->whereNull('loans.returned_at')
            ->orderBy('loans.due_at')
            ->get([
                'loans.id',
                'books.isbn',
                'books.title',
                'books.author',
                'books.genre',
                'books.year_published',
                'users.name as borrower_name',
                'users.login_id as borrower_id',
                'loans.borrowed_at',
                'loans.due_at',
                'loans.returned_at',
                'loans.status',
            ]);
    }

    public function loansForUser(int $userId): Collection
    {
        return DB::table('loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->where('loans.user_id', $userId)
            ->orderByDesc('loans.borrowed_at')
            ->get([
                'loans.id',
                'books.isbn',
                'books.title',
                'books.author',
                'books.genre',
                'books.year_published',
                'loans.borrowed_at',
                'loans.due_at',
                'loans.returned_at',
                'loans.status',
            ]);
    }

    public function historyForUser(int $userId): Collection
    {
        return $this->loansForUser($userId);
    }

    public function loanHistory(): Collection
    {
        return DB::table('loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->join('users', 'users.id', '=', 'loans.user_id')
            ->orderByDesc('loans.borrowed_at')
            ->get([
                'loans.id',
                'books.isbn',
                'books.title',
                'books.author',
                'books.genre',
                'books.year_published',
                'users.name as borrower_name',
                'users.login_id as borrower_id',
                'loans.borrowed_at',
                'loans.due_at',
                'loans.returned_at',
                'loans.status',
            ]);
    }

    public function overdueCount(): int
    {
        return DB::table('loans')
            ->whereNull('returned_at')
            ->whereDate('due_at', '<', now()->toDateString())
            ->count();
    }

    public function reservedCount(): int
    {
        $query = DB::table('reservations')->where('status', 'Reserved');

        if (Schema::hasColumn('reservations', 'quantity')) {
            return (int) $query->sum('quantity');
        }

        return (int) $query->count();
    }

    public function activeLoanCount(): int
    {
        return DB::table('loans')->whereNull('returned_at')->count();
    }

    public function bookCount(): int
    {
        return (int) DB::table('books')->sum('quantity');
    }

    public function availableBookCount(): int
    {
        return (int) DB::table('books')->sum('available_quantity');
    }

    public function userCounts(): array
    {
        return [
            'librarian' => DB::table('users')->where('role', 'librarian')->count(),
            'instructor' => DB::table('users')->where('role', 'instructor')->count(),
            'student' => DB::table('users')->where('role', 'student')->count(),
            'guest' => DB::table('users')->where('role', 'guest')->count(),
            'active' => DB::table('users')->where('status', 'active')->count(),
            'inactive' => DB::table('users')->where('status', 'inactive')->count(),
        ];
    }

    public function createAnnouncement(array $data, ?int $createdBy = null): int
    {
        $announcementId = (int) DB::table('announcements')->insertGetId([
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $data['audience'] ?? 'public',
            'published_at' => $data['published_at'] ?? now()->toDateString(),
            'status' => $data['status'] ?? 'Published',
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (($data['status'] ?? 'Published') === 'Published') {
            $this->notifyAnnouncementAudience(
                $data['audience'] ?? 'public',
                $data['title'],
                $data['body'],
                $announcementId
            );
        }

        return $announcementId;
    }

    public function updateAnnouncement(int $announcementId, array $data): void
    {
        $current = DB::table('announcements')->where('id', $announcementId)->first();

        DB::table('announcements')->where('id', $announcementId)->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $data['audience'] ?? 'public',
            'published_at' => $data['published_at'] ?? null,
            'status' => $data['status'] ?? 'Published',
            'updated_at' => now(),
        ]);

        $wasPublished = $current?->status === 'Published';
        $isPublished = ($data['status'] ?? 'Published') === 'Published';

        if (! $wasPublished && $isPublished) {
            $this->notifyAnnouncementAudience(
                $data['audience'] ?? 'public',
                $data['title'],
                $data['body'],
                $announcementId
            );
        }
    }

    public function deleteAnnouncement(int $announcementId): void
    {
        DB::table('announcements')->where('id', $announcementId)->delete();
    }

    public function announcements(?string $audience = null, ?string $search = null, bool $includePublic = true): Collection
    {
        return DB::table('announcements')
            ->when($audience !== null && $audience !== '', function ($query) use ($audience, $includePublic) {
                if ($includePublic && in_array($audience, ['librarian', 'instructor', 'student', 'guest'], true)) {
                    return $query->whereIn('audience', ['public', 'all', $audience]);
                }

                return $query->where('audience', $audience);
            })
            ->when($includePublic && ($audience === null || $audience === ''), function ($query) {
                return $query->whereIn('audience', ['public', 'all']);
            })
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $term = '%'.trim($search).'%';

                return $query->where(function ($builder) use ($term) {
                    $builder->where('title', 'like', $term)
                        ->orWhere('body', 'like', $term)
                        ->orWhere('audience', 'like', $term);
                });
            })
            ->orderByDesc('published_at')
            ->get();
    }

    public function notifyAnnouncementAudience(string $audience, string $title, string $body, ?int $announcementId = null): int
    {
        $recipientIds = $this->announcementRecipientIds($audience);

        if ($recipientIds->isEmpty()) {
            return 0;
        }

        $timestamp = now();
        $rows = $recipientIds->map(fn (int $userId) => [
            'user_id' => $userId,
            'type' => 'announcement',
            'title' => $title,
            'body' => $body,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all();

        DB::table('notifications')->insert($rows);

        return count($rows);
    }

    public function announcementRecipientIds(string $audience): Collection
    {
        $roles = match ($audience) {
            'all' => ['librarian', 'instructor', 'student', 'guest'],
            'librarian', 'instructor', 'student', 'guest' => [$audience],
            default => ['guest'],
        };

        return DB::table('users')
            ->whereIn('role', $roles)
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('id');
    }

    public function recentActivities(
        int $limit = 8,
        ?string $action = null,
        ?string $search = null,
        ?string $role = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection {
        $hasDescription = Schema::hasColumn('activity_logs', 'description');
        $selectColumns = [
            'activity_logs.id',
            'activity_logs.action',
            'activity_logs.subject_type',
            'activity_logs.subject_id',
            'activity_logs.details',
            'activity_logs.created_at',
            'users.name as user_name',
        ];

        return DB::table('activity_logs')
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->when($action !== null && trim($action) !== '', fn ($query) => $query->where('activity_logs.action', $action))
            ->when($role !== null && trim($role) !== '', fn ($query) => $query->where('users.role', $role))
            ->when($dateFrom !== null && trim($dateFrom) !== '', fn ($query) => $query->whereDate('activity_logs.created_at', '>=', $dateFrom))
            ->when($dateTo !== null && trim($dateTo) !== '', fn ($query) => $query->whereDate('activity_logs.created_at', '<=', $dateTo))
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $term = '%'.trim($search).'%';

                return $query->where(function ($builder) use ($term) {
                    $builder->where('activity_logs.action', 'like', $term)
                        ->orWhere('activity_logs.subject_type', 'like', $term)
                        ->orWhere('activity_logs.subject_id', 'like', $term)
                        ->orWhere('activity_logs.details', 'like', $term)
                        ->orWhere('users.name', 'like', $term)
                        ->orWhere('users.role', 'like', $term);
                });
            })
            ->orderByDesc('activity_logs.created_at')
            ->limit($limit)
            ->get($hasDescription
                ? array_merge(
                    array_slice($selectColumns, 0, 2),
                    ['activity_logs.description'],
                    array_slice($selectColumns, 2)
                )
                : $selectColumns
            );
    }

    public function mostBorrowedBooksForMonth(?string $month = null, int $limit = 5): Collection
    {
        $start = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return DB::table('loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->whereBetween('loans.borrowed_at', [$start, $end])
            ->selectRaw('books.isbn, books.title, books.author, books.genre, COUNT(*) as borrow_count')
            ->groupBy('books.isbn', 'books.title', 'books.author', 'books.genre')
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();
    }

    public function borrowedGenresForMonth(?string $month = null): Collection
    {
        $start = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return DB::table('loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->whereBetween('loans.borrowed_at', [$start, $end])
            ->selectRaw('books.genre, COUNT(*) as borrow_count')
            ->groupBy('books.genre')
            ->orderByDesc('borrow_count')
            ->get();
    }

    public function borrowTrend(int $months = 6): Collection
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        return DB::table('loans')
            ->whereDate('borrowed_at', '>=', $start->toDateString())
            ->orderBy('borrowed_at')
            ->get(['borrowed_at'])
            ->groupBy(fn ($loan) => Carbon::parse($loan->borrowed_at)->format('Y-m'))
            ->map(fn ($items, $month) => (object) [
                'month' => $month,
                'borrow_count' => $items->count(),
            ])
            ->values();
    }

    public function logActivity(?int $userId, string $action, ?string $subjectType = null, ?int $subjectId = null, array $details = []): void
    {
        $payload = [
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'details' => json_encode($details),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('activity_logs', 'description')) {
            $payload['description'] = trim(ucfirst(str_replace('_', ' ', $action)).($subjectType ? ' '.$subjectType : '').($subjectId ? ' #'.$subjectId : ''));
        }

        DB::table('activity_logs')->insert($payload);
    }

    public function stats(): array
    {
        return [
            'books'           => $this->bookCount(),
            'available_books' => $this->availableBookCount(),
            'reservations'    => $this->reservedCount(),
            'loans'           => $this->activeLoanCount(),
            'overdue'         => $this->overdueCount(),
            'users'           => $this->userCounts(),
            'fines_collected' => $this->totalFinesCollected(),
            'fines_unpaid'    => $this->totalFinesUnpaid(),
        ];
    }

    protected function formatBook(object $book): array
    {
        return [
            'id' => $book->id,
            'isbn' => $book->isbn,
            'title' => $book->title,
            'author' => $book->author,
            'genre' => $book->genre,
            'year' => $book->year_published,
            'quantity' => $book->quantity,
            'available_quantity' => $book->available_quantity,
            'location' => $book->location ?? null,
            'status' => $book->status,
            'description' => $book->description,
            'avg_rating' => isset($book->avg_rating) && $book->avg_rating !== null ? round((float) $book->avg_rating, 1) : null,
            'review_count' => isset($book->review_count) ? (int) $book->review_count : 0,
        ];
    }

    public function totalFinesCollected(): float
    {
        return (float) DB::table('fines')->where('status', 'Paid')->sum('amount');
    }

    public function totalFinesUnpaid(): float
    {
        return (float) DB::table('fines')->where('status', 'Unpaid')->sum('amount');
    }

    public function calculateAndStoreFines(): int
    {
        $overdue = DB::table('loans')
            ->whereNull('returned_at')
            ->whereDate('due_at', '<', now()->toDateString())
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('fines')
                      ->whereColumn('fines.loan_id', 'loans.id')
                      ->where('fines.status', 'Paid');
            })
            ->get(['id', 'user_id', 'due_at']);

        $count = 0;
        foreach ($overdue as $loan) {
            $days = max(0, Carbon::parse($loan->due_at)->startOfDay()->diffInDays(now()->startOfDay()));
            $amount = $days * (float) config('library.fine_amount_per_day', 5);

            $existing = DB::table('fines')->where('loan_id', $loan->id)->first();
            if ($existing) {
                DB::table('fines')->where('id', $existing->id)->update([
                    'overdue_days' => $days,
                    'amount'       => $amount,
                    'updated_at'   => now(),
                ]);
            } else {
                DB::table('fines')->insert([
                    'loan_id'      => $loan->id,
                    'user_id'      => $loan->user_id,
                    'overdue_days' => $days,
                    'amount'       => $amount,
                    'status'       => 'Unpaid',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    public function hasUnpaidFines(int $userId): bool
    {
        return DB::table('fines')
            ->where('user_id', $userId)
            ->where('status', 'Unpaid')
            ->exists();
    }

    public function finesForUser(int $userId): Collection
    {
        return DB::table('fines')
            ->join('loans', 'loans.id', '=', 'fines.loan_id')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->where('fines.user_id', $userId)
            ->orderByDesc('fines.created_at')
            ->get([
                'fines.id', 'fines.amount', 'fines.overdue_days',
                'fines.status', 'fines.created_at',
                'books.title', 'books.isbn',
                'loans.due_at', 'loans.borrowed_at',
            ]);
    }

    public function allFines(?string $status = null): Collection
    {
        return DB::table('fines')
            ->join('loans', 'loans.id', '=', 'fines.loan_id')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->join('users', 'users.id', '=', 'fines.user_id')
            ->when($status, fn ($q) => $q->where('fines.status', $status))
            ->orderByDesc('fines.created_at')
            ->get([
                'fines.id', 'fines.amount', 'fines.overdue_days',
                'fines.status', 'fines.created_at',
                'books.title', 'books.isbn',
                'users.name as borrower_name',
                'users.login_id as borrower_id',
                'loans.due_at',
            ]);
    }

    public function markFinePaid(int $fineId, int $processedBy): bool
    {
        $rows = DB::table('fines')->where('id', $fineId)->update([
            'status'       => 'Paid',
            'paid_at'      => now(),
            'processed_by' => $processedBy,
            'updated_at'   => now(),
        ]);

        return $rows > 0;
    }

    public function waiveFine(int $fineId, int $processedBy): bool
    {
        $rows = DB::table('fines')->where('id', $fineId)->update([
            'status'       => 'Waived',
            'processed_by' => $processedBy,
            'updated_at'   => now(),
        ]);

        return $rows > 0;
    }

    public function reviewsForBook(string $isbn): Collection
    {
        $useBookId = Schema::hasColumn('book_reviews', 'book_id');

        $query = DB::table('book_reviews')
            ->join('users', 'users.id', '=', 'book_reviews.user_id');

        if ($useBookId) {
            $query->join('books', 'books.id', '=', 'book_reviews.book_id')
                ->where('books.isbn', $isbn);
        } else {
            $query->join('books', 'books.isbn', '=', 'book_reviews.book_isbn')
                ->where('book_reviews.book_isbn', $isbn);
        }

        return $query->orderByDesc('book_reviews.created_at')->get([
            'book_reviews.id',
            'book_reviews.rating',
            Schema::hasColumn('book_reviews', 'review_text') ? 'book_reviews.review_text as review' : 'book_reviews.review',
            'book_reviews.created_at',
            'users.name as reviewer_name',
        ]);
    }

    public function averageRating(string $isbn): ?float
    {
        $useBookId = Schema::hasColumn('book_reviews', 'book_id');

        $query = DB::table('book_reviews');

        if ($useBookId) {
            $query->join('books', 'books.id', '=', 'book_reviews.book_id')
                ->where('books.isbn', $isbn);
        } else {
            $query->join('books', 'books.isbn', '=', 'book_reviews.book_isbn')
                ->where('book_reviews.book_isbn', $isbn);
        }

        $avg = $query->avg('book_reviews.rating');

        return $avg ? round((float) $avg, 1) : null;
    }

    public function upsertReview(int $userId, string $isbn, int $rating, ?string $review): void
    {
        $book = $this->bookByIsbn($isbn);

        if (! $book) {
            return;
        }

        $useBookId = Schema::hasColumn('book_reviews', 'book_id');
        $hasReviewText = Schema::hasColumn('book_reviews', 'review_text');

        $existingQuery = DB::table('book_reviews')->where('user_id', $userId);
        $existingQuery = $useBookId
            ? $existingQuery->where('book_id', $book->id)
            : $existingQuery->where('book_isbn', $isbn);

        $existing = $existingQuery->first();

        $payload = [
            'rating' => $rating,
            'updated_at' => now(),
        ];

        if ($useBookId) {
            $payload['book_id'] = $book->id;
        }

        if (Schema::hasColumn('book_reviews', 'book_isbn')) {
            $payload['book_isbn'] = $isbn;
        }

        if (Schema::hasColumn('book_reviews', 'review')) {
            $payload['review'] = $review;
        }

        if ($hasReviewText) {
            $payload['review_text'] = $review;
        }

        if ($existing) {
            DB::table('book_reviews')->where('id', $existing->id)->update($payload);
        } else {
            DB::table('book_reviews')->insert(array_merge($payload, [
                'user_id' => $userId,
                'created_at' => now(),
            ]));
        }
    }

    public function topRatedBooks(int $limit = 5): Collection
    {
        $ratings = $this->bookRatingsLookup();

        return DB::table('books')
            ->get(['id', 'isbn', 'title', 'author', 'genre'])
            ->map(function ($book) use ($ratings) {
                $match = $ratings['by_id'][$book->id] ?? $ratings['by_isbn'][$this->normalizeIsbn($book->isbn)] ?? null;
                $book->avg_rating = $match['avg_rating'] ?? null;
                $book->review_count = $match['review_count'] ?? 0;

                return $book;
            })
            ->filter(fn ($book) => $book->review_count > 0)
            ->sortByDesc('avg_rating')
            ->take($limit)
            ->values();
    }

    public function notify(int $userId, string $type, string $title, string $body): void
    {
        DB::table('notifications')->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function notificationsForUser(int $userId, int $limit = 20): Collection
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function unreadCount(int $userId): int
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markNotificationsRead(int $userId): void
    {
        DB::table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    public function markNotificationRead(int $userId, int $notificationId): bool
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now(), 'updated_at' => now()]) > 0;
    }

    public function fullStats(): array
    {
        return array_merge($this->stats(), [
            'fines_collected' => $this->totalFinesCollected(),
            'fines_unpaid'    => $this->totalFinesUnpaid(),
            'top_rated'       => $this->topRatedBooks(5),
        ]);
    }
}