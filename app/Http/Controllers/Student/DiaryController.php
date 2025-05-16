<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Diary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Thêm use Auth
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreDiaryRequest; // Thêm use Request
use App\Http\Requests\UpdateDiaryRequest; // Thêm use Request
use App\Models\DiaryComment;

class DiaryController extends Controller
{   
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Diary::class); // Kiểm tra quyền xem danh sách

    $diaries = Auth::user()->diaries() // Chỉ lấy nhật ký của user đang đăng nhập
                   ->orderBy('diary_date', 'desc') // Sắp xếp mới nhất lên đầu
                   ->paginate(10); // Phân trang (ví dụ 10 mục/trang)

    return view('student.diaries.index', compact('diaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Diary::class); // Kiểm tra quyền tạo
    return view('student.diaries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiaryRequest $request)
    {
        $this->authorize('create', Diary::class); // Kiểm tra quyền

    $validatedData = $request->validated();
    $validatedData['user_id'] = Auth::id();
    $validatedData['status'] = 'draft'; // Mặc định là bản nháp

    Diary::create($validatedData);

    return redirect()->route('student.diaries.index')
                     ->with('success', 'Nhật ký đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Diary $diary)
    {
        $this->authorize('view', $diary);
        // Eager load user viết, reviewer, và comments cùng user của comments
        $diary->load(['user', 'reviewer', 'comments.user']); // Thêm 'reviewer' vào đây
        return view('student.diaries.show', compact('diary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diary $diary)
    {
        $this->authorize('update', $diary); // Kiểm tra quyền cập nhật
    return view('student.diaries.edit', compact('diary'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiaryRequest $request, Diary $diary)
    {
        $this->authorize('update', $diary); // Kiểm tra quyền

    $validatedData = $request->validated();
    // Có thể thêm logic cập nhật status nếu cần (ví dụ: Submitted)
    // $validatedData['status'] = 'submitted';

    $diary->update($validatedData);

    return redirect()->route('student.diaries.show', $diary)
                     ->with('success', 'Nhật ký đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diary $diary)
    {
        $this->authorize('delete', $diary); // Kiểm tra quyền xóa

    $diary->delete();

    return redirect()->route('student.diaries.index')
                     ->with('success', 'Nhật ký đã được xóa thành công.');
    }

    /**
 * Lưu phản hồi mới của Sinh viên vào nhật ký.
 */
public function storeComment(Request $request, Diary $diary)
{
    // Quan trọng: Kiểm tra SV có quyền comment (là chủ sở hữu diary) không?
    // Dùng 'update' policy vì chỉ chủ sở hữu mới được update/comment
    $this->authorize('update', $diary);

    $request->validate([
        'content' => 'required|string|min:5',
    ], [
        'content.required' => 'Vui lòng nhập nội dung phản hồi.',
        'content.min' => 'Nội dung phản hồi phải có ít nhất 5 ký tự.',
    ]);

    // Tạo comment mới
    $diary->comments()->create([
        'user_id' => Auth::id(), // ID của Sinh viên đang đăng nhập
        'content' => $request->input('content'),
    ]);

    // Optional: Cập nhật trạng thái nhật ký thành 'revised' nếu cần
    // $diary->status = 'revised';
    // $diary->save();

    // Optional: Gửi notification cho Admin
    // ... (Logic tìm và gửi cho Admin) ...

    return redirect()->route('student.diaries.show', $diary)
                     ->with('success', 'Đã gửi phản hồi thành công.');
}

}
