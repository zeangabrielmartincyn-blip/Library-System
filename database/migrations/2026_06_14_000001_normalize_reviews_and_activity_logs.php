<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_reviews', function (Blueprint $table) {
            $table->foreignId('book_id')->nullable()->after('user_id')->constrained('books')->nullOnDelete();
            $table->text('review_text')->nullable()->after('rating');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('action');
        });

        DB::table('book_reviews')->orderBy('id')->get(['id', 'book_isbn', 'review'])->each(function ($review) {
            $bookId = DB::table('books')->where('isbn', $review->book_isbn)->value('id');

            DB::table('book_reviews')->where('id', $review->id)->update([
                'book_id' => $bookId,
                'review_text' => $review->review,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('book_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_id');
            $table->dropColumn('review_text');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};


