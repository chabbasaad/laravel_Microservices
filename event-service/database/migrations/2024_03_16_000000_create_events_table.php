<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('date');
            $table->string('location');
            $table->integer('max_tickets');
            $table->integer('available_tickets');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('creator_id');
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            
            // Speakers information
            $table->json('speakers')->nullable()->comment('Array of speakers with their details: [{"name", "bio", "photo_url", "company", "position", "topic", "speaking_time"}]');
            
            // Sponsors information
            $table->json('sponsors')->nullable()->comment('Array of sponsors with their details: [{"name", "logo_url", "website_url", "tier", "type"}]');
            
            $table->timestamps();
            
            $table->index(['status', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
