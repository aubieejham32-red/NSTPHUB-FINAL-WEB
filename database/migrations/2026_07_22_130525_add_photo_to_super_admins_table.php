<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('super_admins', function ($table) {
        $table->string('photo')->nullable()->after('password');
    });
}

public function down(): void
{
    Schema::table('super_admins', function ($table) {
        $table->dropColumn('photo');
    });
}
};
