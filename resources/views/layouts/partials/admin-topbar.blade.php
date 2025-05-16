{{-- resources/views/layouts/partials/admin-topbar.blade.php --}}
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    {{-- Sidebar Toggle (Topbar) --}}
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
        <i class="fa fa-bars"></i>
    </button>

    {{-- Topbar Search --}}
    <form class="d-none d-sm-inline-block form-inline ms-md-3 my-2 my-md-0 mw-100 navbar-search">
         <div class="input-group"> <input type="text" class="form-control bg-light border-0 small" placeholder="Tìm kiếm..." aria-label="Search"> <div class="input-group-append"> <button class="btn btn-primary" type="button"><i class="fas fa-search fa-sm"></i></button> </div> </div>
    </form>

    {{-- Topbar Navbar --}}
    <ul class="navbar-nav ms-auto">

        {{-- Nav Item - Search Dropdown (Visible Only XS) --}}
        <li class="nav-item dropdown no-arrow d-sm-none">
             <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-search fa-fw"></i> </a>
             <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--grow-in" aria-labelledby="searchDropdown"> <form class="form-inline me-auto w-100 navbar-search"> <div class="input-group"> <input type="text" class="form-control bg-light border-0 small" placeholder="Tìm kiếm..."> <div class="input-group-append"> <button class="btn btn-primary" type="button"> <i class="fas fa-search fa-sm"></i> </button> </div> </div> </form> </div>
        </li>

        {{-- Nav Item - Alerts (Thông báo) --}}
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Thông báo">
                <i class="fas fa-bell fa-fw text-primary"></i>
                @auth {{-- Đảm bảo chỉ query khi đã đăng nhập --}}
                    @php
                        // Lấy số lượng thông báo chưa đọc một cách an toàn
                        $currentUser = Auth::user();
                        $unreadNotificationsCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;
                    @endphp
                    @if($unreadNotificationsCount > 0)
                        {{-- ID này để JavaScript có thể cập nhật --}}
                        <span class="badge bg-danger badge-counter" id="unreadNotificationsCount">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
                    @else
                         {{-- Vẫn tạo span này nhưng ẩn đi để JS có thể tìm thấy và show() khi có noti mới --}}
                        <span class="badge bg-danger badge-counter" id="unreadNotificationsCount" style="display: none;">0</span>
                    @endif
                @endauth
            </a>
            <!-- Dropdown - Alerts -->
            {{-- ID này để JavaScript có thể thêm/xóa item hoặc cập nhật UI --}}
            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown" style="min-width: 320px;" id="notificationsDropdownContainer">
                 <h6 class="dropdown-header"> Trung tâm Thông báo </h6>
                 @auth
                    {{-- Lấy 5 thông báo mới nhất, cả đọc và chưa đọc để hiển thị --}}
                    @php
                        // Lấy 5 thông báo mới nhất, ưu tiên chưa đọc lên đầu
                        $notificationsToDisplay = Auth::user()
                                                    ? Auth::user()->notifications()
                                                                ->orderByRaw('read_at IS NULL DESC, created_at DESC') // Ưu tiên chưa đọc, rồi mới nhất
                                                                ->take(5)
                                                                ->get()
                                                    : collect();
                    @endphp
                    @forelse ($notificationsToDisplay as $notification)
                        @php $isUnread = is_null($notification->read_at); @endphp
                        {{-- Thêm class 'notification-item' và các data attributes --}}
                        <a @class([
                                'dropdown-item', 'd-flex', 'align-items-center',
                                'notification-item', // Class để JS bắt sự kiện
                                // 'bg-light' => $isUnread, // Có thể bỏ class này nếu JS sẽ xử lý style
                            ])
                           href="{{ $notification->data['url'] ?? '#' }}"
                           data-notification-id="{{ $notification->id }}"
                           data-mark-as-read-url="{{ route('notifications.read', $notification->id) }}"
                           title="{{ $notification->data['message'] ?? 'Bạn có thông báo mới.' }}">
                            <div class="me-3">
                                <div class="icon-circle {{ $notification->data['icon_bg_class'] ?? ($isUnread ? 'bg-primary' : 'bg-secondary') }}">
                                    <i class="{{ $notification->data['icon_class'] ?? 'fas fa-bell' }} text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                                <span @class(['fw-bold' => $isUnread, 'text-muted' => !$isUnread])>
                                    {{ Str::limit($notification->data['message'] ?? 'Bạn có thông báo mới.', 70) }} {{-- Giới hạn độ dài message --}}
                                </span>
                            </div>
                        </a>
                    @empty
                        <span class="dropdown-item text-center small text-gray-500" id="noNotificationsMessage">Không có thông báo nào.</span>
                    @endforelse

                    {{-- Chỉ hiển thị nếu có bất kỳ thông báo nào (đã đọc hoặc chưa đọc) --}}
                    @if(Auth::user()->notifications()->exists())
                        <a class="dropdown-item text-center small text-gray-500" href="#">{{-- {{ route('admin.notifications.index') }} --}}Xem tất cả</a>
                        {{-- Chỉ hiển thị nếu còn thông báo chưa đọc --}}
                        @if($unreadNotificationsCount > 0)
                            <a class="dropdown-item text-center small text-primary mark-all-as-read-btn" href="#"
                               data-mark-all-url="{{ route('notifications.markAllAsRead') }}">Đánh dấu tất cả đã đọc</a>
                        @endif
                    @endif
                 @else
                    <span class="dropdown-item text-center small text-gray-500">Vui lòng đăng nhập.</span>
                 @endauth
            </div>
        </li>

        {{-- Nav Item - Messages (Hộp thư) --}}
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link"
               href="{{ Auth::check() && Auth::user()->hasRole('SinhVien') ? route('student.messages.index') : (Auth::check() && Auth::user()->hasRole('Admin') ? route('admin.messages.index') : '#') }}"
               id="messagesDropdown"
               role="button"
               title="Tin nhắn">
                <i class="fas fa-envelope fa-fw text-primary"></i>
                @auth @php $unreadMessagesCount = \App\Models\Message::where('receiver_id', Auth::id())->whereNull('read_at')->count(); @endphp @if($unreadMessagesCount > 0) <span class="badge bg-danger badge-counter">{{ $unreadMessagesCount > 9 ? '9+' : $unreadMessagesCount }}</span> @endif @endauth
            </a>
        </li>

        {{-- ===== NÚT GỬI TIN NHẮN (CHỈ DÀNH CHO SINH VIÊN) ===== --}}
        @auth
            @if(Auth::user()->hasRole('SinhVien'))
                <li class="nav-item no-arrow mx-1">
                     <a class="nav-link" href="{{ route('student.messages.create') }}" title="Gửi tin nhắn mới">
                        <i class="fas fa-paper-plane fa-fw text-primary"></i>
                     </a>
                </li>
            @endif
        @endauth

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <span class="me-3 d-none d-lg-inline text-gray-600 small"> {{ Auth::user()->name ?? 'Guest' }} </span> <img class="img-profile rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" src="{{ Auth::user()->profile_photo_url ?? asset('img/undraw_profile.svg') }}"> </a>
              <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown"> <a class="dropdown-item" href="{{ route('profile.edit') }}"> <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Hồ sơ </a> <div class="dropdown-divider"></div> <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"> <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Đăng xuất </a> </div>
        </li>
    </ul>
</nav>