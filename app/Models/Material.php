<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "materials";

    protected $fillable = ["code", "name", "category_id", "uom_id", "warehouse_id"];

    protected $hidden = ["category_id", "uom_id", "warehouse_id", "created_at", "deleted_at"];

    public function category()
    {
        return $this->belongsTo(Category::class)
            ->select("id", "name", "deleted_at")
            ->withTrashed();
    }

    public function uom()
    {
        return $this->belongsTo(UOM::class)
            ->select("id", "code", "description", "deleted_at")
            ->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)
            ->select("id", "code", "name", "deleted_at")
            ->withTrashed();
    }
}
