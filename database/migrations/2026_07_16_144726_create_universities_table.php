<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {

            $table->id();

            // Institution Information
            $table->string('name');
            $table->string('acronym',30);
            $table->enum('type',['Public','Private']);
            $table->enum('campus_type',['Main Campus','Extension']);

            $table->string('email')->unique();
            $table->string('contact_number',20)->nullable();
            $table->string('website')->nullable();

            $table->string('logo')->nullable();

            // Address

            $table->string('region');
            $table->string('province');
            $table->string('city');
            $table->string('barangay');
            $table->string('zip_code',10);
            $table->text('complete_address');

            // NSTP Configuration

            $table->string('academic_year');
            $table->string('semester');

            /*
                CWTS,LTS,ROTC
            */
            $table->json('components');

            $table->integer('max_students');

            // Generated Values

            $table->string('access_code')->unique();

            $table->enum('status',['ACTIVE','INACTIVE'])
                    ->default('ACTIVE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};