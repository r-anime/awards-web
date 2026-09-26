<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if foreign key exists and drop it if it does
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'app_answers' 
            AND COLUMN_NAME = 'question_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE app_answers DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        Schema::table('app_answers', function (Blueprint $table) {
            // Change the column type from integer to string
            $table->string('question_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_answers', function (Blueprint $table) {
            // Change the column type back to integer
            $table->unsignedBigInteger('question_id')->change();
            
            // Re-add the foreign key constraint
            $table->foreign('question_id')->references('id')->on('app_questions');
        });
    }
};
