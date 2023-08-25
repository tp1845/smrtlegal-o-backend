<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    static public function getIdRoleBySlug($slug) {
        $role = self::where('slug', $slug)->first();
        return $role ? $role->id : false;
    }
}
