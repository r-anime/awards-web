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
        // Convert existing staff_credits from key-value format to role/name array format
        $results = DB::table('results')->whereNotNull('staff_credits')->get();
        
        foreach ($results as $result) {
            $staffCredits = $result->staff_credits;
            
            // Skip if already null or empty
            if (empty($staffCredits)) {
                continue;
            }
            
            // Try to decode JSON
            $decoded = json_decode($staffCredits, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Check if it's already in the correct format (array of objects with role/name)
                $isCorrectFormat = true;
                if (!empty($decoded)) {
                    $firstItem = reset($decoded);
                    if (!is_array($firstItem) || !isset($firstItem['role']) || !isset($firstItem['name'])) {
                        $isCorrectFormat = false;
                    }
                }
                
                if ($isCorrectFormat) {
                    // Already in correct format, skip
                    continue;
                }
                
                // Convert old key-value format to new role/name format
                $newFormat = [];
                
                foreach ($decoded as $key => $value) {
                    if (is_string($key) && is_string($value)) {
                        // Convert key to proper case and create role/name structure
                        $role = ucfirst(strtolower($key));
                        $newFormat[] = [
                            'role' => $role,
                            'name' => $value
                        ];
                    }
                }
                
                // Update the record with the new format
                if (!empty($newFormat)) {
                    DB::table('results')
                        ->where('id', $result->id)
                        ->update(['staff_credits' => json_encode($newFormat)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to simple key-value format
        $results = DB::table('results')->whereNotNull('staff_credits')->get();
        
        foreach ($results as $result) {
            $staffCredits = $result->staff_credits;
            $decoded = json_decode($staffCredits, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Check if it's in the new format (array of objects with role/name)
                $isNewFormat = false;
                if (!empty($decoded)) {
                    $firstItem = reset($decoded);
                    if (is_array($firstItem) && isset($firstItem['role']) && isset($firstItem['name'])) {
                        $isNewFormat = true;
                    }
                }
                
                if ($isNewFormat) {
                    // Convert from role/name format back to simple key-value
                    $oldFormat = [];
                    foreach ($decoded as $item) {
                        if (is_array($item) && isset($item['role']) && isset($item['name'])) {
                            $oldFormat[strtolower($item['role'])] = $item['name'];
                        }
                    }
                    
                    if (!empty($oldFormat)) {
                        DB::table('results')
                            ->where('id', $result->id)
                            ->update(['staff_credits' => json_encode($oldFormat)]);
                    }
                }
            }
        }
    }
};
