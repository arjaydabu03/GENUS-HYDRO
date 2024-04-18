<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UOM extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "uom";

    protected $fillable = ["code", "description"];

    protected $hidden = ["created_at"];
}
