<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "role";

    protected $fillable = ["name", "access_permission"];

    protected $casts = ["access_permission" => "json"];

    protected $hidden = ["created_at"];
}
