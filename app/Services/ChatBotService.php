<?php

namespace App\Services;

class ChatBotService
{
    public function reply(string $message, array $context = []): string
    {
        return $this->fallbackReply($message, $context, 'Using the library database context only.');
    }

    protected function fallbackReply(string $message, array $context, string $reason): string
    {
        $text = strtolower(trim($message));
        $databaseContext = $context['database_context'] ?? [];
        $summary = $databaseContext['summary'] ?? [];

        if (str_contains($text, 'announcement') || str_contains($text, 'notice') || str_contains($text, 'latest')) {
            $announcements = $databaseContext['announcements'] ?? [];
            if (is_iterable($announcements) && count($announcements) > 0) {
                $items = collect($announcements)->take(3)->map(function ($item) {
                    $title = trim((string) ($item['title'] ?? 'Announcement'));
                    $body = trim((string) ($item['body'] ?? ''));
                    $publishedAt = trim((string) ($item['published_at'] ?? 'recent'));
                    $bodyText = $body !== '' ? " - {$body}" : '';

                    return "- {$title} (published {$publishedAt}){$bodyText}";
                })->implode(PHP_EOL);

                return "Here are the latest announcements:".PHP_EOL.$items;
            }
        }

        if (str_contains($text, 'fine')) {
            $myFines = $databaseContext['my_fines'] ?? [];
            if (is_iterable($myFines) && count($myFines) > 0) {
                $fines = collect($myFines);
                $unpaid = $fines->where('status', 'Unpaid');

                if ($unpaid->isNotEmpty()) {
                    $unpaidTotal = number_format((float) $unpaid->sum('amount'), 2);
                    $titles = $unpaid->pluck('title')->take(5)->implode(', ');

                    return "You have unpaid fines totaling ₱{$unpaidTotal}, linked to: {$titles}.";
                }

                return "You have no outstanding fines. All your previous fines have been settled.";
            }

            return "You have no fines on record.";
        }

        if (str_contains($text, 'borrow') || str_contains($text, 'loan') || str_contains($text, 'borrowed')) {
            $activeLoans = $databaseContext['my_active_loans'] ?? [];
            if (is_iterable($activeLoans) && count($activeLoans) > 0) {
                $titles = collect($activeLoans)->pluck('title')->take(5)->implode(', ');

                return "You currently have these borrowed books: {$titles}.";
            }

            return 'You do not currently have any borrowed books.';
        }

        if (str_contains($text, 'reserv') || str_contains($text, 'hold')) {
            $reservations = $databaseContext['my_reservations'] ?? [];
            if (is_iterable($reservations) && count($reservations) > 0) {
                $titles = collect($reservations)->pluck('title')->take(5)->implode(', ');

                return "You currently have these reservations: {$titles}.";
            }

            return 'You do not currently have any reservations.';
        }

        if (str_contains($text, 'profile') || str_contains($text, 'account') || str_contains($text, 'dashboard')) {
            $role = $context['role'] ?? 'user';
            $name = $context['user_name'] ?? 'User';
            $booksTotal = $summary['books_total'] ?? null;
            $booksAvailable = $summary['books_available'] ?? null;

            $parts = ["You are signed in as {$name} ({$role})."];
            if ($booksAvailable !== null && $booksTotal !== null) {
                $parts[] = "The library currently has {$booksAvailable} available books out of {$booksTotal} total.";
            }

            return implode(' ', $parts);
        }

        if (($context['role'] ?? 'user') === 'librarian') {
            if (str_contains($text, 'loan') || str_contains($text, 'borrow')) {
                $recentLoans = $databaseContext['recent_loans'] ?? [];
                if (is_iterable($recentLoans) && count($recentLoans) > 0) {
                    $items = collect($recentLoans)->take(3)->map(function ($item) {
                        $title = trim((string) ($item['title'] ?? 'Unknown title'));
                        $borrower = trim((string) ($item['borrower'] ?? 'Unknown borrower'));
                        $dueAt = trim((string) ($item['due_at'] ?? 'unknown due date'));

                        return "- {$title} for {$borrower} (due {$dueAt})";
                    })->implode(PHP_EOL);

                    return "Here are the recent active loans:".PHP_EOL.$items;
                }
            }

            if (str_contains($text, 'reservation') || str_contains($text, 'hold')) {
                $recentReservations = $databaseContext['recent_reservations'] ?? [];
                if (is_iterable($recentReservations) && count($recentReservations) > 0) {
                    $items = collect($recentReservations)->take(3)->map(function ($item) {
                        $title = trim((string) ($item['title'] ?? 'Unknown title'));
                        $borrower = trim((string) ($item['borrower'] ?? 'Unknown borrower'));
                        $reservedAt = trim((string) ($item['reserved_at'] ?? 'unknown date'));

                        return "- {$title} reserved by {$borrower} ({$reservedAt})";
                    })->implode(PHP_EOL);

                    return "Here are the recent active reservations:".PHP_EOL.$items;
                }
            }
        }

        if (str_contains($text, 'available') && isset($summary['books_available'], $summary['books_total'])) {
            return "There are {$summary['books_available']} available books out of {$summary['books_total']} total books in the library.";
        }

        if (str_contains($text, 'book') || str_contains($text, 'catalog') || str_contains($text, 'author') || str_contains($text, 'title')) {
            $books = $databaseContext['books'] ?? [];
            if (is_iterable($books) && count($books) > 0) {
                $items = collect($books)->take(3)->map(function ($book) {
                    $status = ((int) ($book['available_quantity'] ?? 0) > 0) ? 'available' : 'unavailable';
                    $reason = $book['reason'] ?? 'matched your topic';
                    $title = trim((string) ($book['title'] ?? 'Untitled'));
                    $author = trim((string) ($book['author'] ?? 'Unknown author'));
                    $genre = trim((string) ($book['genre'] ?? 'Unknown genre'));

                    return "- {$title} by {$author} ({$genre}, {$status}) - {$reason}";
                })->implode(PHP_EOL);

                $searchTerm = trim((string) ($databaseContext['search_term'] ?? ''));
                $header = $searchTerm !== '' ? "Here are related books for '{$searchTerm}':" : 'Here are the closest matching books:';

                return $header.PHP_EOL.$items;
            }
        }

        if (str_contains($text, 'announcement') || str_contains($text, 'notice')) {
            return 'I could not load the latest announcements at the moment, but the announcement section in the dashboard should still show them.';
        }

        return "{$reason} I can still help with library-related questions about books, reservations, loans, fines, and announcements.";
    }
}