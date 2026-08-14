<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "admin" role has been removed from the application. All of its
 * capabilities (user management, book editing, fines, reviews, reports,
 * charts, notifications, etc.) now live under the "librarian" role instead.
 * Any existing accounts that were still on role=admin are migrated to
 * role=librarian so they keep working and don't get locked out.
 *
 * login_id values like "ADMIN001" are left as-is (they're just identifiers),
 * but if one happens to collide with an existing librarian login_id it is
 * left untouched and only the role is changed — a collision there would be
 * pre-existing bad data, not something this migration should silently fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'librarian']);
    }

    public function down(): void
    {
        // Not reversible: once merged into librarian, we can no longer tell
        // which librarian accounts were originally admins.
    }
};
