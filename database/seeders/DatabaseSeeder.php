<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Có thể comment hoặc xóa dòng này
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import Hash

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Gọi các seeder theo đúng thứ tự phụ thuộc
         $this->call([
            RoleSeeder::class,
            SchoolSeeder::class, // Chạy School trước Major
            MajorSeeder::class,
            // Gọi các seeder khác nếu có
        ]);

         // Tạo User Admin chính
         // updateOrCreate tìm user có email này, nếu chưa có thì tạo mới
         User::updateOrCreate(
             ['email' => 'admin@pizitech.com'], // Điều kiện tìm kiếm
             [ // Dữ liệu để tạo hoặc cập nhật
                 'name' => 'Admin Pizitech',
                 'password' => Hash::make('az0344123778'), // Đặt mật khẩu admin ở đây!
                 'role_id' => 1, // ID của Role Admin
                 'profile_status' => 'approved', // Admin được duyệt sẵn
                 'email_verified_at' => now(), // Xác thực email luôn
                 // Các trường khác của Admin có thể để null hoặc giá trị mặc định
             ]
         );

         // (Tùy chọn) Tạo user Sinh viên mẫu (nếu muốn test)
         // User::factory(5)->create(['role_id' => 2]); // Tạo 5 sinh viên mẫu với role_id = 2

         // (Tùy chọn) Nếu bạn muốn tạo sinh viên mẫu với đầy đủ thông tin hơn:
         $schoolA = \App\Models\School::where('name', 'Đại học A')->first();
         $majorIT_A = \App\Models\Major::where('name', 'Công nghệ thông tin (A)')->first();
         if ($schoolA && $majorIT_A) {
             User::updateOrCreate(
                 ['email' => 'student1@example.com'],
                 [
                     'name' => 'Sinh Vien 1',
                     'password' => Hash::make('sinhvien1'),
                     'role_id' => 2,
                     'mssv' => 'SV001',
                     'phone' => '0123456789',
                     'school_id' => $schoolA->id,
                     'major_id' => $majorIT_A->id,
                     'profile_status' => 'approved', // Hoặc 'pending' để test duyệt
                     'email_verified_at' => now(),
                 ]
             );
         }
    }
}