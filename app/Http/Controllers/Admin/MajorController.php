<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major; // Import Model Major
use App\Models\School; // Import Model School để lấy danh sách trường
use App\Http\Requests\Admin\StoreMajorRequest; // Import Store Request
use App\Http\Requests\Admin\UpdateMajorRequest; // Import Update Request
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Major::class);

        // Lấy danh sách chuyên ngành, eager load thông tin 'school'
        $majors = Major::with('school')->latest()->paginate(10);
        return view('admin.majors.index', compact('majors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Major::class);

        // Lấy danh sách tất cả các trường để hiển thị trong dropdown
        $schools = School::orderBy('name')->get();
        return view('admin.majors.create', compact('schools')); // Truyền biến $schools ra view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMajorRequest $request) // Dùng StoreMajorRequest
    {
        $this->authorize('create', Major::class);

        Major::create($request->validated());

        return redirect()->route('admin.majors.index')
                         ->with('success', 'Đã thêm chuyên ngành mới thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Major $major)
    {
         $this->authorize('view', $major);
         return redirect()->route('admin.majors.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Major $major) // Dùng Route Model Binding
    {
        $this->authorize('update', $major);

        // Lấy danh sách trường để hiển thị dropdown
        $schools = School::orderBy('name')->get();
        // Truyền cả major cần sửa và danh sách schools
        return view('admin.majors.edit', compact('major', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMajorRequest $request, Major $major) // Dùng UpdateMajorRequest
    {
        $this->authorize('update', $major);

        $major->update($request->validated());

        return redirect()->route('admin.majors.index')
                         ->with('success', 'Đã cập nhật thông tin chuyên ngành thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major)
    {
         $this->authorize('delete', $major);

         try {
            $majorName = $major->name;
            $major->delete();
            return redirect()->route('admin.majors.index')
                             ->with('success', "Đã xóa chuyên ngành '{$majorName}' thành công!");
        } catch (\Exception $e) {
             // Log::error("Lỗi xóa chuyên ngành ID {$major->id}: " . $e->getMessage());
             if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                 return redirect()->route('admin.majors.index')
                                  ->with('error', "Không thể xóa chuyên ngành '{$major->name}' vì vẫn còn sinh viên thuộc chuyên ngành này.");
             }
             return redirect()->route('admin.majors.index')
                              ->with('error', 'Đã xảy ra lỗi khi xóa chuyên ngành.');
        }
    }
}