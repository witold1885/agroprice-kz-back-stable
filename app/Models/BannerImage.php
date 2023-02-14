<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'path',
        'link',
        'active',
        'date_from',
        'date_to',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
