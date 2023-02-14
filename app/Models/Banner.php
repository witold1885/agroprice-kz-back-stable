<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'autoplay',
        'duration',
    ];

    public function bannerImages()
    {
        return $this->hasMany(BannerImage::class);
    }

    public function getActiveImages()
    {
        $activeImages = [];
        foreach (BannerImage::where('banner_id', $this->id)->get() as $i => $image) {
            if ($image->active) {
                $activeImages[] = [
                    'num'  => $i + 1,
                    'path' => $image->path,
                    'link' => $image->link,
                    'show' => $i === 0
                ];
            }
        }
        return $activeImages;
    }
}
