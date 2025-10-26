<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'description',
        'video_path',
        'activity_date',
        'user_id',
        'is_private',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'is_private' => 'boolean',
    ];

    // ============================================================================
    // العلاقات (Relationships)
    // ============================================================================

    /**
     * العلاقة مع المستخدم (الأدمن الذي أضاف النشاط)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع المفضلة (Many-to-Many)
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'activity_id', 'user_id')
                    ->withTimestamps();
    }

    // ============================================================================
    // Helper Methods
    // ============================================================================

    /**
     * التحقق من أن المستخدم أضاف هذا النشاط للمفضلة
     */
    public function isFavoritedBy($user)
    {
        if (!$user) {
            return false;
        }
        
        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * الحصول على رابط الفيديو الكامل
     */
    public function getVideoUrlAttribute()
    {
        return Storage::url($this->video_path);
    }
}
