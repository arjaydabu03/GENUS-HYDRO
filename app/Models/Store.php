<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "users_store";
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
    ];

    function scope_order()
    {
        return $this->hasMany(TagStoreLocation::class, "account_id", "id");
    }
}
