<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'user_id', 'category_id', 'type_id', 'category', 'type', 'content'];

    public function typeDocument() {
        return $this->hasOne(DocumentType::class, 'id', 'type_id');
    }

    public function category() {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
