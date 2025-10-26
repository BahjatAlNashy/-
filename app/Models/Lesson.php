<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;

class Lesson extends Model
{
    //
    protected $fillable =
    [
'title',
'saying_date',
'description',
'user_id',
'is_private',
    ];

    public function sources ()
    {
        return $this->hasMany(Source::class);
    }


    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
    {
        // المستخدمون الذين فضلوا هذا الدرس: العديد لـ العديد عبر جدول 'lesson_favorites'
        return $this->belongsToMany(User::class, 'lesson_favorites');
    }

    public function favorites()
    {
        return $this->hasMany(LessonFavorite::class);
    }

    public function isFavoritedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->favorites()->where('user_id', $user->id)->exists(); 
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
