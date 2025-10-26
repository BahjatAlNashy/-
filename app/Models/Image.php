<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'image_url',
        'user_id',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * العلاقة مع المستخدم (صاحب الصورة)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع المستخدمين الذين أضافوها للمفضلة
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_images');
    }

    /**
     * التحقق من أن المستخدم أضاف الصورة للمفضلة
     */
    public function isFavoritedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->favoritedBy->contains($user);
    }
}
