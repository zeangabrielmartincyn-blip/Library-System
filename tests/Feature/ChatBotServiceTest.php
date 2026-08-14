<?php

namespace Tests\Feature;

use App\Services\ChatBotService;
use Tests\TestCase;

class ChatBotServiceTest extends TestCase
{
    public function test_reply_uses_database_context_only_when_answering(): void
    {
        $service = new ChatBotService();

        $reply = $service->reply('How many books are available?', [
            'role' => 'student',
            'user_name' => 'Jane',
            'database_context' => [
                'summary' => [
                    'books_total' => 10,
                    'books_available' => 7,
                ],
                'books' => [
                    [
                        'title' => 'Clean Code',
                        'author' => 'Robert C. Martin',
                        'genre' => 'Programming',
                        'available_quantity' => 2,
                        'reason' => 'keyword match',
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('7 available books', $reply);
        $this->assertStringContainsString('10 total', $reply);
        $this->assertStringNotContainsString('Gemini', $reply);
    }
}
