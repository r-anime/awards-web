<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppQuestion;
use App\Models\User;

class AppScore extends Model
{
    // use HasFactory;

    public function question(){
        return $this->belongsTo(AppQuestion::class, 'id', 'question_id');
    }

    public function applicant(){
        return $this->belongsTo(User::class, 'id', 'applicant_id');
    }

    public function scorer(){
        return $this->belongsTo(User::class, 'id', 'scorer_id');
    }
}
