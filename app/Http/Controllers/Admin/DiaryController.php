<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diary;
use App\Models\User; // Cần để lấy danh sách SV cho filter (tùy chọn)
use Illuminate\Http\Request; // Import đã có
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Sử dụng để gọi authorize
use App\Models\DiaryComment;
use Illuminate\Support\Facades\Auth; // Import đã có

class DiaryController extends Controller
{
    use AuthorizesRequests; // Sử dụng trait

    /**
     * Hiển thị danh sách tất cả nhật ký.
     */
    public function index(Request $request) // Giữ nguyên
    {
        $this->authorize('viewAny', Diary::class);

        $query = Diary::with('user')
                     ->orderBy('diary_date', 'desc')
                     ->orderBy('created_at', 'desc');

        if ($request->filled('student_id')) {
            $query->where('user_id', $request->input('student_id'));
        }

        $diaries = $query->paginate(15);

        $students = User::whereHas('role', function($q){
                            $q->where('name', 'SinhVien');
                        })->orderBy('name')->get();

        return view('admin.diaries.index', compact('diaries', 'students'));
    }

    /**
     * Hiển thị chi tiết một nhật ký.
     */
    public function show(Diary $diary) // Giữ nguyên
    {
        $this->authorize('view', $diary);
        $diary->load(['user', 'comments.user']);
        return view('admin.diaries.show', compact('diary'));
    }

    /**
     * Lưu comment chung của Admin (khác với review/đánh giá).
     */
    public function storeComment(Request $request, Diary $diary) // Giữ nguyên
    {
        $this->authorize('view', $diary);

        $request->validate([
            'content' => 'required|string|min:5',
        ], [
            'content.required' => 'Vui lòng nhập nội dung nhận xét.',
            'content.min' => 'Nội dung nhận xét phải có ít nhất 5 ký tự.',
        ]);

        $diary->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        if ($diary->status !== 'commented') {
            $diary->status = 'commented';
            $diary->save();
        }

        // Optional: Gửi notification cho sinh viên
        // $diary->user->notify(new DiaryCommentedNotification($diary, Auth::user()));

        return redirect()->route('admin.diaries.show', $diary)
                         ->with('success', 'Đã gửi nhận xét thành công.');
    }

    // <<< ====== THÊM PHƯƠNG THỨC NÀY ====== >>>
    /**
     * Store the admin's review/comment and grade for a diary entry.
     * Lưu nhận xét/đánh giá chính thức của Admin cho một bài nhật ký.
     */
    public function storeReview(Request $request, Diary $diary)
    {
        // Kiểm tra quyền (có thể dùng policy 'update' hoặc 'review' riêng)
        $this->authorize('update', $diary); // Giả sử dùng quyền 'update' chung

        // Validate dữ liệu gửi lên từ form
        $validated = $request->validate([
            'admin_comment' => 'required|string', // Nhận xét là bắt buộc
            'grade' => 'nullable|integer|min:0|max:10', // Điểm là tùy chọn, số nguyên từ 0-10
        ], [
            'admin_comment.required' => 'Vui lòng nhập nội dung nhận xét/đánh giá.',
            'grade.integer' => 'Điểm phải là một số nguyên.',
            'grade.min' => 'Điểm không được nhỏ hơn 0.',
            'grade.max' => 'Điểm không được lớn hơn 10.',
        ]);

        // Cập nhật bản ghi nhật ký với thông tin review
        $diary->update([
            'admin_comment' => $validated['admin_comment'],
            'grade' => $validated['grade'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
            // Cập nhật luôn status thành 'reviewed' hoặc tương tự nếu cần
            'status' => 'reviewed', // Giả sử bạn muốn cập nhật status khi có review chính thức
        ]);

        // // Optional: Gửi thông báo cho sinh viên về việc đã được review
        // if ($diary->student) { // Sử dụng relationship đã định nghĩa trong Model
        //     // $diary->student->notify(new DiaryReviewedNotification($diary));
        //     \Log::info("Nhật ký ID {$diary->id} của SV {$diary->user->name} đã được Admin đánh giá.");
        // }

        // Chuyển hướng về trang xem chi tiết nhật ký với thông báo thành công
        return redirect()->route('admin.diaries.show', $diary)
                         ->with('success', 'Đã lưu nhận xét và đánh giá thành công.');
    }
    // <<< ====== KẾT THÚC PHẦN THÊM MỚI ====== >>>

}