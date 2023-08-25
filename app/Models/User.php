<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Models\Setting;
use App\Models\Team;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'fname',
        'lname',
        'phone',
        'avatar',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAvatarAttribute($value) {
        if ($value) {
            return url('/storage') . '/' . str_replace("public/", "", $value);
        }
        return $value;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')->withPivot('email', 'name');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'user_id', 'id');
    }

    public function getName() {
        $user = $this;
        if ($user->fname && $user->lname) {
            return $user->fname . ' ' . $user->fname;
        }

        if ($user->name) {
            return $user->name;
        }

        return $user->email;
    }
}
