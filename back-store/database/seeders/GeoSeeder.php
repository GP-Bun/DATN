<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        // Đọc file JSON đã gộp 3 cấp
        $path = database_path('seeders/data/provinces_with_districts_and_wards.json');
        $data = json_decode(File::get($path), true);

        foreach ($data as $province) {
            // ===== Province =====
            $provinceModel = Province::updateOrCreate(
                ['code' => $province['Code']],
                [
                    'name' => $province['Name'],
                    'slug' => $province['CodeName'],
                    'type' => $province['FullName'],
                ]
            );

            // ===== Districts =====
            foreach ($province['Districts'] ?? [] as $district) {
                $districtModel = District::updateOrCreate(
                    ['code' => $district['Code']],
                    [
                        'name' => $district['Name'],
                        'slug' => $district['CodeName'],
                        'type' => $district['FullName'],
                        'province_id' => $provinceModel->id,
                    ]
                );

                // ===== Wards =====
                foreach ($district['Wards'] ?? [] as $ward) {
                    Ward::updateOrCreate(
                        ['code' => $ward['Code']],
                        [
                            'name' => $ward['Name'],
                            'slug' => $ward['CodeName'],
                            'type' => $ward['FullName'],
                            'district_id' => $districtModel->id,
                        ]
                    );
                }
            }
        }
    }
}
