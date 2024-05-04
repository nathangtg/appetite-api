<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OrderedItems extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'order_id',
        'menu_id',
        'quantity',
        'price',
        'total',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
