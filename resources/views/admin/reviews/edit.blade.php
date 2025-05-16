{{-- resources/views/admin/reviews/edit.blade.php --}}

@extends('layouts.admin') {{-- Hoặc @extends('layouts.app') nếu layout của bạn tên khác --}}

@section('title', 'Chỉnh sửa Nhận xét Sinh viên')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Nhận xét cho Sinh viên: {{ $review->student->name }} (MSSV: {{ $review->student->mssv }})</h1>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Thông báo lỗi -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin Nhận xét</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.reviews.update', $review->id) }}">
                @csrf
                @method('PUT')

                {{-- Thông tin Sinh viên (chỉ hiển thị, không cho sửa trực tiếp ở đây) --}}
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Sinh viên được nhận xét:</label>
                    <p class="form-control-plaintext">{{ $review->student->name }} - MSSV: {{ $review->student->mssv }}</p>
                    {{-- Nếu bạn muốn cho phép thay đổi sinh viên khi edit (không khuyến khích cho review),
                         bạn cần thêm một dropdown giống như ở trang create và bỏ input user_id trong validation của controller update --}}
                </div>

                <div class="mb-3">
                    <label for="review_period" class="form-label">Kỳ nhận xét (Ví dụ: Giữa kỳ, Cuối kỳ)</label>
                    <input type="text"
                           class="form-control @error('review_period') is-invalid @enderror"
                           id="review_period"
                           name="review_period"
                           value="{{ old('review_period', $review->review_period) }}">
                    @error('review_period')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung nhận xét <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror"
                              id="content"
                              name="content"
                              rows="5"
                              required>{{ old('content', $review->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-sm"></i> Cập nhật Nhận xét
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-light ml-2">
                        Hủy bỏ
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

{{-- Nếu bạn muốn sử dụng Rich Text Editor (ví dụ TinyMCE) cho phần nội dung --}}
{{-- @push('scripts')
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#content',
    plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons template paste textpattern',
    toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
    // Thêm các tùy chọn khác nếu cần
    // height: 300, // Chiều cao của editor
  });
</script>
@endpush --}}