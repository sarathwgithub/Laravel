<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;
    protected $fillable = ['grn_no', 'invoice_no', 'supplier_name', 'remark'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
