<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagAccount extends Model
{
    use HasFactory;

    protected $table = "tagaccountlocation";

    protected $fillable = ["account_id", "location_id", "location_code"];

    protected $hidden = ["account_id", "created_at", "updated_at", "deleted_at"];
}
