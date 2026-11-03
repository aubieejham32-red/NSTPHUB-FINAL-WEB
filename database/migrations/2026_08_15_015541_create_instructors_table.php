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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | University
            |--------------------------------------------------------------------------
            */

            $table->foreignId('university_id')
                ->constrained('universities')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Instructor Information
            |--------------------------------------------------------------------------
            */

            $table->string('full_name');

            $table->string('username')
                ->unique();

            $table->string('email')
                ->unique();

            $table->string('phone_number', 30)
                ->nullable();

            $table->string('password');

            /*
            |--------------------------------------------------------------------------
            | NSTP Component
            |--------------------------------------------------------------------------
            |
            | Example:
            | CWTS
            | ROTC
            | LTS
            |
            */

            $table->string('component');

            /*
            |--------------------------------------------------------------------------
            | Account Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('component');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};