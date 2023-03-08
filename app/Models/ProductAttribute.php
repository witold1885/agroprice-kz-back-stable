<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'filter_group_id',
        'filter_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function filterGroup()
    {
        return $this->belongsTo(FilterGroup::class);
    }

    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }

}
