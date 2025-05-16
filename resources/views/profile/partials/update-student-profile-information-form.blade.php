{{-- resources/views/profile/partials/update-student-profile-information-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-weight-medium text-dark">Thông tin Hồ sơ cá nhân</h2>
        <p class="mt-1 small text-muted">Cập nhật thông tin hồ sơ và địa chỉ email của tài khoản.</p>
        {{-- Hiển thị trạng thái hồ sơ --}}
        <p class="mt-2">
            <strong>Trạng thái Hồ sơ:</strong>
            <span @class([
                'badge fw-bold', // fw-bold để chữ đậm hơn
                'bg-warning text-dark' => $user->profile_status === 'pending',
                'bg-success' => $user->profile_status === 'approved',
                'bg-danger' => $user->profile_status === 'rejected',
                'bg-secondary' => !in_array($user->profile_status, ['pending', 'approved', 'rejected'])
            ])>
                {{ match($user->profile_status) {
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Bị từ chối',
                    default => ucfirst($user->profile_status ?? 'Chưa nộp')
                } }}
            </span>
             {{-- Hiển thị lý do từ chối nếu có --}}
             @if($user->profile_status === 'rejected' && $user->rejection_reason)
                <small class="d-block text-danger fst-italic mt-1">Lý do từ chối: {{ $user->rejection_reason }}</small>
             @endif
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- Ảnh đại diện (Giống của Admin) --}}
         <div class="mb-3">
            <label for="photo" class="form-label">Ảnh đại diện</label>
            <div class="mt-2 d-flex align-items-center gap-3">
                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                <div>
                    <input class="form-control form-control-sm @error('photo') is-invalid @enderror" type="file" name="photo" id="photo" accept="image/png, image/jpeg, image/jpg">
                     <small class="text-muted d-block mt-1">Chọn ảnh mới JPG, PNG (Tối đa 2MB).</small>
                     @error('photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Tên --}}
        <div class="mb-3">
            <label for="name" class="form-label">Tên <span class="text-danger">*</span></label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Email (Thường không cho SV sửa) --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ $user->email }}" readonly disabled />
            <small class="text-muted">Email không thể thay đổi.</small>
            {{-- Phần xác thực email (giữ nguyên nếu cần) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
               {{-- ... code xác thực email ... --}}
            @endif
        </div>

        {{-- MSSV --}}
        <div class="mb-3">
            <label for="mssv" class="form-label">Mã Sinh Viên (MSSV)</label>
            {{-- Cho phép sửa nếu hồ sơ chưa duyệt, ngược lại thì readonly --}}
            <input type="text" id="mssv" name="mssv" value="{{ old('mssv', $user->mssv) }}"
                   class="form-control @error('mssv') is-invalid @enderror"
                   {{ $user->profile_status === 'approved' ? 'readonly disabled' : '' }}>
             @if($user->profile_status === 'approved')
                <small class="text-muted">MSSV không thể thay đổi sau khi hồ sơ đã duyệt.</small>
             @endif
            @error('mssv') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        {{-- Số điện thoại --}}
        <div class="mb-3">
             <label for="phone_number" class="form-label">Số điện thoại</label>
             <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel"
                    class="form-control @error('phone_number') is-invalid @enderror">
             @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Chọn Trường --}}
        <div class="mb-3">
            <label for="school_id" class="form-label">Trường</label>
            <select id="school_id" name="school_id"
                    class="form-select @error('school_id') is-invalid @enderror"
                    {{ $user->profile_status === 'approved' ? 'disabled' : '' }}> {{-- Disable nếu đã duyệt --}}
                <option value="">-- Chọn trường --</option>
                @isset($schools)
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                @endisset
            </select>
             @if($user->profile_status === 'approved')
                <input type="hidden" name="school_id" value="{{ $user->school_id }}"> {{-- Gửi giá trị cũ nếu bị disable --}}
                <small class="text-muted">Trường không thể thay đổi sau khi hồ sơ đã duyệt.</small>
             @endif
            @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Chọn Chuyên ngành --}}
        <div class="mb-3">
            <label for="major_id" class="form-label">Chuyên ngành</label>
            {{-- TODO: Lọc động theo trường đã chọn --}}
            <select id="major_id" name="major_id"
                    class="form-select @error('major_id') is-invalid @enderror"
                    {{ $user->profile_status === 'approved' ? 'disabled' : '' }}> {{-- Disable nếu đã duyệt --}}
                <option value="">-- Chọn ngành --</option>
                 @isset($majors)
                     @foreach ($majors as $major)
                         {{-- Có thể lọc trước $majors theo school_id của user nếu cần --}}
                         <option value="{{ $major->id }}" {{ old('major_id', $user->major_id) == $major->id ? 'selected' : '' }}>
                             {{ $major->name }}
                         </option>
                     @endforeach
                 @endisset
            </select>
             @if($user->profile_status === 'approved')
                <input type="hidden" name="major_id" value="{{ $user->major_id }}"> {{-- Gửi giá trị cũ nếu bị disable --}}
                <small class="text-muted">Ngành không thể thay đổi sau khi hồ sơ đã duyệt.</small>
             @endif
            @error('major_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Nút Lưu --}}
        <div class="d-flex align-items-center gap-4 mt-4">
            <button type="submit" class="btn btn-sm btn-primary shadow-sm text-white">Lưu thay đổi</button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="small text-muted m-0">Đã lưu.</p>
            @endif
        </div>
    </form>
</section>