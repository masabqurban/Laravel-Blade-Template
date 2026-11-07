<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration on top of the starter kit's users table. Plain string
 * columns (not database ENUMs) keep it portable across SQLite, MySQL,
 * MariaDB, PostgreSQL and SQL Server; PHP enums validate the values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('viewer')->index()->after('email');
            $table->string('status', 20)->default('active')->index()->after('role');
            $table->timestamp('last_active_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['role', 'status', 'last_active_at']);
        });
    }
};
