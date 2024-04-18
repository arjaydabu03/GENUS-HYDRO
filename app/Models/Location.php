<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "location";

    protected $fillable = ["sync_id", "code", "name", "deleted_at"];

    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            "location_department",
            "location_id",
            "department_id",
            "sync_id",
            "sync_id"
        );
    }
}
