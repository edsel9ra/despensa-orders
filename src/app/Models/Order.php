<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['remision', 'sede', 'fecha', 'subtotal', 'iva', 'total'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
