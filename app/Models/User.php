<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Resources\UserResource;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "account_code",
        "account_name",
        "mobile_no",

        "location_id",
        "location_code",
        "location",

        "department_id",
        "department_code",
        "department",

        "company_id",
        "company_code",
        "company",

        "role_id",
        "username",
        "password",
    ];

    protected $hidden = ["password", "remember_token"];

    function scope_approval()
    {
        return $this->hasMany(TagAccount::class, "account_id", "id");
    }

    function scope_order()
    {
        return $this->hasMany(TagAccountOrder::class, "account_id", "id");
    }

    function role()
    {
        return $this->belongsTo(Role::class);
    }
}
