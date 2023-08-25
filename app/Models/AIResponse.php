<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIResponse extends Model
{
    protected $table = 'ai_responses';
    protected $fillable = ['content', 'response'];
    protected $dates = ['created_at', 'updated_at'];
}