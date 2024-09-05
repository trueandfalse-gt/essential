<?php
namespace App\Models\Auth;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use HasFactory;
    // use HasApiTokens;
    // use Notifiable;

    public function roles()
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

}
