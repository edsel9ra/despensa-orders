<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nombre' => 'SALSAS ALITAS', 'orden' => 1, 'aplica_iva' => true],
            ['nombre' => 'SALSAS VARIAS', 'orden' => 2, 'aplica_iva' => true],
            ['nombre' => 'ALIÑOS', 'orden' => 3, 'aplica_iva' => true],
            ['nombre' => 'POLLO', 'orden' => 4, 'aplica_iva' => true],
            ['nombre' => 'CARNES Y ABARROTES', 'orden' => 5, 'aplica_iva' => true],
            ['nombre' => 'CANASTILLAS', 'orden' => 6, 'aplica_iva' => true],
            ['nombre' => 'EMPAQUES Y MISCELANEA', 'orden' => 7, 'aplica_iva' => true],
            ['nombre' => 'FLETE CALI', 'orden' => 8, 'aplica_iva' => true],
            ['nombre' => 'PRODUCTOS SIN IVA', 'orden' => 9, 'aplica_iva' => false],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
