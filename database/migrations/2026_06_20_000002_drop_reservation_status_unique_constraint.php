<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This constraint allowed only ONE reservation row per (user, book, status)
     * combination for all time — not just one active reservation. Since
     * declineReservation()/cancelReservation() reuse the same status strings
     * ("Declined", "Cancelled") across a user's history with the same book,
     * the second time a user's reservation for the same book was declined (or
     * cancelled), the UPDATE collided with the earlier row and crashed with
     * UniqueConstraintViolationException. Active-reservation uniqueness is now
     * enforced in app code (see LibraryRepository::reserveBook) using a locked
     * transaction instead of a blanket DB constraint.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // The composite unique index currently also supplies the index
            // required by the user_id foreign key. Add that standalone index
            // before removing the composite constraint on MySQL.
            $table->index('user_id', 'reservations_user_id_index_for_foreign');
            $table->dropUnique('reservations_user_id_book_id_status_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_user_id_index_for_foreign');
            $table->unique(['user_id', 'book_id', 'status']);
        });
    }
};
