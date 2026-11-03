<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('university_administrators')) {
            return;
        }

        if (!Schema::hasColumn('university_administrators', 'temporary_password')) {
            Schema::table('university_administrators', function (Blueprint $table) {
                $table->text('temporary_password')->nullable()->after('password');
            });
        }

        if (!Schema::hasColumn('university_administrators', 'must_change_password')) {
            Schema::table('university_administrators', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(true)->after('temporary_password');
            });
        }

        if (!Schema::hasColumn('university_administrators', 'remember_token')) {
            Schema::table('university_administrators', function (Blueprint $table) {
                $table->rememberToken();
            });
        }
    }

    public function down(): void
    {
        // Repair migration intentionally keeps compatible columns in place.
    }
};
