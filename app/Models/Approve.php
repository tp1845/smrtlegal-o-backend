<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'document_id', 'is_final'];
}
