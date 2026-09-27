<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'uom_id',
    ];

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }

    public function price()
    {
        return $this->hasOne(Price::class)->latestOfMany();
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
