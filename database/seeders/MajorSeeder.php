<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Models\School; // Import School để lấy ID

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy ID của trường đã tạo
        $schoolA = School::where('name', 'Đại học A')->first();
        $schoolB = School::where('name', 'Đại học B')->first();

        if ($schoolA) {
            Major::updateOrCreate(['school_id' => $schoolA->id, 'name' => 'Công nghệ thông tin (A)']);
            Major::updateOrCreate(['school_id' => $schoolA->id, 'name' => 'Quản trị kinh doanh (A)']);
        }
        if ($schoolB) {
            Major::updateOrCreate(['school_id' => $schoolB->id, 'name' => 'Kỹ thuật phần mềm (B)']);
            Major::updateOrCreate(['school_id' => $schoolB->id, 'name' => 'Marketing (B)']);
        }
         // Thêm các ngành khác nếu muốn
    }
}