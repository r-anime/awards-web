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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->foreignId('category_id');
            $table->string('name', length:255);
            $table->text('image');
            $table->foreignId('entry_id');
            $table->integer('jury_rank');
            $table->integer('public_rank');
            $table->text('description');
            $table->json('staff_credits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
