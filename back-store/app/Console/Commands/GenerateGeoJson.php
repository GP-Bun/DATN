<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateGeoJson extends Command
{
    protected $signature = 'geo:generate-json';
    protected $description = 'Gộp province_old.json, district_old.json, ward_old.json thành JSON 3 cấp';

    public function handle()
{
    $provinces = json_decode(File::get(database_path('seeders/data/province_old.json')), true);
    $districts = json_decode(File::get(database_path('seeders/data/district_old.json')), true);
    $wards = json_decode(File::get(database_path('seeders/data/ward_old.json')), true);

    $result = [];

    foreach ($provinces as $provinceCode => $province) {
        $provinceItem = [
            'Code' => $province['code'],
            'Name' => $province['name'],
            'CodeName' => $province['slug'],
            'FullName' => $province['name_with_type'],
            'Districts' => []
        ];

        // Lấy district theo parent_code = province.code
        $districtsInProvince = array_filter($districts, fn($d) => $d['parent_code'] == $province['code']);

        foreach ($districtsInProvince as $districtCode => $district) {
            $districtItem = [
                'Code' => $district['code'],
                'Name' => $district['name'],
                'CodeName' => $district['slug'],
                'FullName' => $district['name_with_type'],
                'Wards' => []
            ];

            // Lấy ward theo parent_code = district.code
            $wardsInDistrict = array_filter($wards, fn($w) => $w['parent_code'] == $district['code']);

            foreach ($wardsInDistrict as $wardCode => $ward) {
                $districtItem['Wards'][] = [
                    'Code' => $ward['code'],
                    'Name' => $ward['name'],
                    'CodeName' => $ward['slug'],
                    'FullName' => $ward['name_with_type']
                ];
            }

            $provinceItem['Districts'][] = $districtItem;
        }

        $result[] = $provinceItem;
    }

    File::put(
        database_path('seeders/data/provinces_with_districts_and_wards.json'),
        json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $this->info('✅ Đã tạo file provinces_with_districts_and_wards.json thành công!');
}

}
