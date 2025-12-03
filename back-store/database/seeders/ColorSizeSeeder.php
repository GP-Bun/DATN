<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;
use App\Models\Size;

class ColorSizeSeeder extends Seeder
{
    public function run(): void
    {
        // Màu mẫu
        $colors = [
            ['name' => 'Đỏ',   'code' => '#FF0000'],
            ['name' => 'Xanh', 'code' => '#0000FF'],
            ['name' => 'Đen',  'code' => '#000000'],
            ['name' => 'Trắng','code' => '#FFFFFF'],
        ];

        foreach ($colors as $c) {
            Color::firstOrCreate(
                ['name' => $c['name']], 
                ['code' => $c['code']]
            );
        }

        $sizes = [38, 39, 40, 41, 42];
        foreach ($sizes as $s) {
            Size::firstOrCreate(['value' => $s]);
        }
    }
}   
