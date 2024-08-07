<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Restaurant extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'address',
        'image_path',
        'preparation_time',
        'cuisine',
        'price_range',
        'average_rating',
        'is_open',
        'number_of_tables',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->ratings()->avg('rating'), 2);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function scopeClose($query)
    {
        return $query->where('is_open', false);
    }

    protected $appends = ['average_rating'];

}
