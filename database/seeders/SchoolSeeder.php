<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::updateOrCreate(['name' => 'Đại học A'], ['address' => 'Địa chỉ A']);
        School::updateOrCreate(['name' => 'Đại học B'], ['address' => 'Địa chỉ B']);
        // Thêm các trường khác nếu muốn
    }
}