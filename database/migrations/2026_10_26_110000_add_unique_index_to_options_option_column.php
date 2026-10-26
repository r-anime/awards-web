<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Option::get() and Option::set() both use the first row for a key, so any later duplicates
        // were never read. Remove them so the unique index can be added
        $laterDuplicateIds = DB::table('options as later')
            ->join('options as earlier', function ($join) {
                $join->on('later.option', '=', 'earlier.option')
                    ->on('later.id', '>', 'earlier.id');
            })
            ->distinct()
            ->pluck('later.id');

        DB::table('options')->whereIn('id', $laterDuplicateIds)->delete();

        Schema::table('options', function (Blueprint $table) {
            $table->unique('option');
        });
    }

    /**
     * Reverse the migrations. Removed duplicates aren't restored.
     */
    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            // Array form, so Laravel derives the index name (options_option_unique) from the column
            $table->dropUnique(['option']);
        });
    }
};
