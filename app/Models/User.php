<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;




class User extends Authenticatable 
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function poems()
     {
    return $this->hasMany(Poem::class);    
    }

    public function lessons()
    {
   return $this->hasMany(Lesson::class);    
   }
   public function activities()
   {
    return $this->hasMany(Activity::class);
   }

   public function favoriteActivities()
   {
    return $this->belongsToMany(Activity::class, 'favorites', 'user_id', 'activity_id');
   }

   public function favoritePoems()
{
    // القصائد المفضلة: العديد لـ العديد عبر جدول 'favorites'
    return $this->belongsToMany(Poem::class, 'favorites');
}

public function favoriteLessons()
{
    // الدروس المفضلة: العديد لـ العديد عبر جدول 'lesson_favorites'
    return $this->belongsToMany(Lesson::class, 'lesson_favorites');
}

public function favoriteSayings()
{
    // الأقوال المفضلة: العديد لـ العديد عبر جدول 'saying_favorites'
    return $this->belongsToMany(Saying::class, 'saying_favorites');
}

public function comments()
    {
        return $this->hasMany(Comment::class);
    }

public function images()
{
    return $this->hasMany(Image::class);
}

public function favoriteImages()
{
    return $this->belongsToMany(Image::class, 'favorite_images');
}
    
}
