<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location_Department extends Model
{
    use HasFactory;

    protected $table = "location_department";

    protected $fillable = ["location_id", "department_id"];
}
