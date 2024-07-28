<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPBan extends Model
{
    use HasFactory;
    protected $table = 'ip_bans';
}
