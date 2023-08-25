<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Team extends Model
{
    use HasFactory;

    public function members() {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')->withPivot('role_id', 'email', 'name', 'user_id');
    }

    public function project() {
        return $this->belongsTo(Project::class, 'id', 'team_id');
    }
}
