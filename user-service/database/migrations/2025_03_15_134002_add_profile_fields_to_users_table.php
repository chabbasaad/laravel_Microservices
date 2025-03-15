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
        Schema::table('users', function (Blueprint $table) {
            // Remove password field as it will be managed by Auth Service
            $table->dropColumn(['password', 'remember_token', 'email_verified_at']);

            // Add profile fields
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('language')->default('en');
            $table->json('preferences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore original fields
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();

            // Remove profile fields
            $table->dropColumn([
                'phone_number',
                'address',
                'city',
                'country',
                'profile_picture',
                'bio',
                'date_of_birth',
                'gender',
                'language',
                'preferences',
                'is_active',
                'last_activity'
            ]);
        });
    }
};
