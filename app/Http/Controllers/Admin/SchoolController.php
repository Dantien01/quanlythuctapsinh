<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Kế thừa Controller cơ bản
use App\Models\School; // Import Model School
use App\Http\Requests\Admin\StoreSchoolRequest; // Import Store Request
use App\Http\Requests\Admin\UpdateSchoolRequest; // Import Update Request
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Import Trait để dùng $this->authorize()
use Illuminate\Http\Request; // Có thể cần nếu dùng Request thường

class SchoolController extends Controller
{
    use AuthorizesRequests; // Sử dụng Trait

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Kiểm tra quyền xem danh sách (sẽ tạo policy sau)
        $this->authorize('viewAny', School::class);

        $schools = School::latest()->paginate(10); // Lấy danh sách trường, mới nhất lên đầu, phân trang
        return view('admin.schools.index', compact('schools')); // Trả về view index với dữ liệu
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền tạo (sẽ tạo policy sau)
        $this->authorize('create', School::class);

        return view('admin.schools.create'); // Chỉ cần hiển thị view form tạo mới
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolRequest $request) // Sử dụng StoreSchoolRequest để validate
    {
        // Kiểm tra quyền tạo (sẽ tạo policy sau)
        $this->authorize('create', School::class);

        // Dữ liệu đã được validate bởi StoreSchoolRequest
        School::create($request->validated()); // Tạo bản ghi mới

        return redirect()->route('admin.schools.index') // Chuyển hướng về trang index
                         ->with('success', 'Đã thêm trường học mới thành công!'); // Kèm thông báo thành công
    }

    /**
     * Display the specified resource.
     * (Thường không cần trang xem chi tiết riêng cho admin trong trường hợp này)
     */
    public function show(School $school)
    {
        $this->authorize('view', $school);
         return redirect()->route('admin.schools.index'); // Hoặc redirect về index
        // Hoặc nếu cần thì tạo view 'admin.schools.show'
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school) // Sử dụng Route Model Binding
    {
        // Kiểm tra quyền sửa (sẽ tạo policy sau)
        $this->authorize('update', $school);

        return view('admin.schools.edit', compact('school')); // Trả về view edit với dữ liệu school
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolRequest $request, School $school) // Dùng UpdateSchoolRequest và Route Model Binding
    {
        // Kiểm tra quyền sửa (sẽ tạo policy sau)
        $this->authorize('update', $school);

        // Dữ liệu đã được validate
        $school->update($request->validated()); // Cập nhật bản ghi

        return redirect()->route('admin.schools.index')
                         ->with('success', 'Đã cập nhật thông tin trường học thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school) // Sử dụng Route Model Binding
    {
        // Kiểm tra quyền xóa (sẽ tạo policy sau)
        $this->authorize('delete', $school);

        try {
            $schoolName = $school->name;
            $school->delete(); // Xóa bản ghi
            return redirect()->route('admin.schools.index')
                             ->with('success', "Đã xóa trường '{$schoolName}' thành công!");
        } catch (\Exception $e) {
             // Ghi log lỗi nếu cần
             // Log::error("Lỗi xóa trường ID {$school->id}: " . $e->getMessage());
            // Kiểm tra xem có phải lỗi khóa ngoại không (do còn Majors/Users)
             if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                 return redirect()->route('admin.schools.index')
                                  ->with('error', "Không thể xóa trường '{$school->name}' vì vẫn còn chuyên ngành hoặc sinh viên thuộc trường này.");
             }
             return redirect()->route('admin.schools.index')
                              ->with('error', 'Đã xảy ra lỗi khi xóa trường học.');
        }
    }
}