<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrCreate tìm bản ghi theo điều kiện đầu tiên,
        // nếu không thấy thì tạo mới với cả 2 điều kiện.
        // Gán ID cứng giúp dễ tham chiếu.
        Role::updateOrCreate(['id' => 1], ['name' => 'Admin']);
        Role::updateOrCreate(['id' => 2], ['name' => 'SinhVien']);
    }
}