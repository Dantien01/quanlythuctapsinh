<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\StudentReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class StudentReviewController extends Controller
{
use AuthorizesRequests; // Sử dụng trait để gọi $this->authorize()
/**
 * Hiển thị danh sách các nhận xét mà Admin này đã viết.
 */
public function index(Request $request)
{
    $this->authorize('viewAny', StudentReview::class); // Kiểm tra quyền

    $query = StudentReview::where('reviewer_id', Auth::id()) // Chỉ lấy nhận xét của admin hiện tại
                          ->with('student') // Lấy kèm thông tin sinh viên được nhận xét
                          ->latest(); // Sắp xếp mới nhất lên đầu

    // Lọc theo Sinh viên nếu có tham số student_id trên URL
    if ($request->filled('student_id')) {
         $query->where('user_id', $request->input('student_id'));
    }

    $reviews = $query->paginate(15); // Phân trang

    // Lấy danh sách sinh viên để hiển thị trong dropdown filter
    $students = User::whereHas('role', fn($q) => $q->where('name', 'SinhVien'))
                     ->orderBy('name')->get(['id', 'name', 'mssv']); // Chỉ lấy các cột cần thiết

    return view('admin.reviews.index', compact('reviews', 'students'));
}

/**
 * Hiển thị form để tạo nhận xét mới.
 */
public function create()
{
    $this->authorize('create', StudentReview::class); // Kiểm tra quyền

    // Lấy danh sách sinh viên đã được duyệt hồ sơ để chọn
    $students = User::whereHas('role', fn($q) => $q->where('name', 'SinhVien'))
                     ->where('profile_status', 'approved') // Chỉ nhận xét SV đã duyệt
                     ->orderBy('name')->get(['id', 'name', 'mssv']);

    return view('admin.reviews.create', compact('students'));
}

/**
 * Lưu nhận xét mới vào database.
 */
public function store(Request $request)
{
    $this->authorize('create', StudentReview::class); // Kiểm tra quyền

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id', // Đảm bảo user_id tồn tại trong bảng users
        'review_period' => 'nullable|string|max:50',
        'content' => 'required|string|min:10',
    ],[
        // Custom error messages (tiếng Việt)
        'user_id.required' => 'Vui lòng chọn sinh viên.',
        'user_id.exists' => 'Sinh viên được chọn không hợp lệ.',
        'content.required' => 'Vui lòng nhập nội dung nhận xét.',
        'content.min' => 'Nội dung nhận xét phải có ít nhất :min ký tự.',
    ]);

    // Kiểm tra kỹ hơn: user_id phải là SinhVien
    $student = User::find($validated['user_id']);
    if (!$student || !$student->hasRole('SinhVien')) {
         return back()->withErrors(['user_id' => 'Người dùng được chọn không phải là sinh viên.'])->withInput();
    }

    // Tạo bản ghi nhận xét mới
    StudentReview::create([
        'user_id' => $validated['user_id'], // ID của sinh viên được nhận xét
        'reviewer_id' => Auth::id(),       // ID của Admin đang đăng nhập
        'review_period' => $validated['review_period'],
        'content' => $validated['content'],
    ]);

    // (Tùy chọn) Gửi thông báo cho sinh viên
    // $student->notify(new NewReviewReceivedNotification(Auth::user()));

    return redirect()->route('admin.reviews.index')
                     ->with('success', 'Đã lưu nhận xét thành công cho sinh viên ' . $student->name);
}
    public function destroy(StudentReview $review) // Sử dụng Route Model Binding
{
    // Optional: Kiểm tra quyền xóa (Policy)
    // $this->authorize('delete', $review);

    $review->delete();

    return redirect()->route('admin.reviews.index')->with('success', 'Xóa nhận xét thành công.');
    }
}   