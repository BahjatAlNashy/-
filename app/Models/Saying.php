<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saying extends Model
{
    protected $fillable = [
        'type',
        'content',
        'is_private',
        'user_id',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    // Constants
    const TYPE_SAYING = 'saying';
    const TYPE_SUPPLICATION = 'supplication';

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع التعليقات
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * العلاقة مع المفضلة
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'saying_favorites')
                    ->withTimestamps();
    }

    /**
     * التحقق من المفضلة
     */
    public function isFavoritedBy(User $user): bool
    {
        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope للأقوال المأثورة
     */
    public function scopeSayings($query)
    {
        return $query->where('type', self::TYPE_SAYING);
    }

    /**
     * Scope للأوراد والأذكار
     */
    public function scopeSupplications($query)
    {
        return $query->where('type', self::TYPE_SUPPLICATION);
    }

    /**
     * Scope للعامة
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }
}
