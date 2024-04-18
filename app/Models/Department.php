<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "department";

    protected $fillable = ["sync_id", "code", "name", "company_id"];

    public function company()
    {
        return $this->hasOne(Company::class, "sync_id", "company_id");
    }
}
