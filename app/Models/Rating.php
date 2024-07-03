<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Rating extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'rating',
        'comment',
        'order_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function avgRating($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)->avg('rating');
    }

    public function scopeAverageRating($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId)->avg('rating');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
