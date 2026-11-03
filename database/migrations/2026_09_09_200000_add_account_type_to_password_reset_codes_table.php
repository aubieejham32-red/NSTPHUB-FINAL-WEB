<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_codes', function (Blueprint $table) {
            $table->string('account_type', 40)
                ->default('university_admin')
                ->after('email');

            $table->index(
                ['email', 'account_type'],
                'password_reset_codes_email_account_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_codes', function (Blueprint $table) {
            $table->dropIndex('password_reset_codes_email_account_type_index');
            $table->dropColumn('account_type');
        });
    }
};
