<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'user_type',
        'user_staus',
        'is_deleted',
        'role',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
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

    public function adminDetails()
    {
        return $this->hasOne(AdminDetail::class, 'user_id');
    }

    public function staffDetails()
    {
        return $this->hasOne(StaffDetail::class, 'user_id');
    }    

    public function studentDetails()
    {
        return $this->hasOne(StudentDetail::class, 'user_id');
    }
}
