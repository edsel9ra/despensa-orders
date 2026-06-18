<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nombre', 'orden', 'aplica_iva'];

    public function items()
    {
        return $this->hasMany(Item::class, 'categoria_id');
    }
}
