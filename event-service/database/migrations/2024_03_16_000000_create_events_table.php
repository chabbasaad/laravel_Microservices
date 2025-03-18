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
            
            // Simple text fields for speakers and sponsors
            $table->text('speakers')->nullable()->comment('Comma-separated list of speakers');
            $table->text('sponsors')->nullable()->comment('Comma-separated list of sponsors');
            
            $table->timestamps();
            
            $table->index(['status', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
