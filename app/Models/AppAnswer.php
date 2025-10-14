<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppQuestion;
use App\Models\User;

class AppAnswer extends Model
{
    // use HasFactory;

    protected $fillable = [
        'question_id',
        'applicant_id',
        'answer',
    ];

    public function question(){
        return $this->belongsTo(AppQuestion::class, 'question_id', 'id');
    }

    public function applicant(){
        return $this->belongsTo(User::class, 'applicant_id', 'id');
    }
}
