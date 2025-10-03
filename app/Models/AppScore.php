<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppQuestion;
use App\Models\User;

class AppScore extends Model
{
    // use HasFactory;

    protected $fillable = [
        'applicant_id',
        'scorer_id',
        'question_id',
        'question_uuid',
        'score',
        'comment',
    ];

    public function question(){
        return $this->belongsTo(AppQuestion::class, 'question_id', 'id');
    }

    public function applicant(){
        return $this->belongsTo(User::class, 'applicant_id', 'id');
    }

    public function scorer(){
        return $this->belongsTo(User::class, 'scorer_id', 'id');
    }
}
