<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['grn_id', 'product_name', 'product_qty'];

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }
}
