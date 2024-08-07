<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Order extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'orders';

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'email',
        'total',
        'status',
        'table_number',
        'order_type',
        'payment_method',
        'payment_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items()
    {
        return $this->hasMany(OrderedItems::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
