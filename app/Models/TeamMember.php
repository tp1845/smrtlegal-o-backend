<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = ['accepted', 'user_id', 'team_id', 'name', 'email', 'role_id'];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function role() {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}
