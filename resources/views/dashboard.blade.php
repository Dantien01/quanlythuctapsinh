{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin') {{-- Hoặc layout bạn dùng cho student mà đã nhúng SB Admin 2 CSS/JS --}}

@php
    // use Carbon\Carbon; // Bỏ comment nếu bạn cần dùng Carbon trực tiếp trong view này
@endphp

@section('title', 'Bảng điều khiển Sinh viên')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển Sinh viên</h1>
        <a href="{{ route('student.messages.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-paper-plane fa-sm text-white-50"></i> Gửi tin nhắn cho Admin
        </a>
    </div>

    {{-- ===== PHẦN HIỂN THỊ THÔNG BÁO ===== --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            {{ session('error') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- ===================================== --}}

    <!-- Row 1: Tổng quan & Tiến độ -->
    <div class="row">

        <!-- Card: Tổng giờ làm -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng giờ làm</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalWorkHours ?? 0, 1) }} giờ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Tỷ lệ chuyên cần -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tỷ lệ chuyên cần
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $attendanceRate = Auth::user()->attendance_rate;
                                @endphp
                                @if(!is_null($attendanceRate))
                                    {{ number_format($attendanceRate, 1) }}%
                                @else
                                    N/A <small class="text-muted fw-normal">(Chưa tính)</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Tiến độ Thực tập -->
        <div class="col-xl-3 col-md-6 mb-4">
             <div class="card border-left-success shadow h-100 py-2">
                 <div class="card-body">
                     <div class="row no-gutters align-items-center">
                         <div class="col">
                             <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tiến độ thực tập</div>
                             <div class="row no-gutters align-items-center mb-1">
                                 <div class="col-auto">
                                     <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $progressData['percentage'] ?? 0 }}%</div>
                                 </div>
                                 <div class="col">
                                     <div class="progress progress-sm mr-2">
                                         <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ $progressData['percentage'] ?? 0 }}%" aria-valuenow="{{ $progressData['percentage'] ?? 0 }}" aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                     </div>
                                 </div>
                             </div>
                             <div class="text-xs text-muted">
                                 Đã hoàn thành: {{ number_format($progressData['completed'] ?? 0, 1) }} / {{ number_format($progressData['total'] ?? 0, 1) }} {{ $progressData['unit'] ?? 'giờ' }}
                             </div>
                         </div>
                         <div class="col-auto">
                             <i class="fas fa-tasks fa-2x text-gray-300"></i>
                         </div>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Card: Đánh giá TB -->
         @isset($feedbackChart['average'])
             <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Đánh giá TB</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $feedbackChart['average'] }}/10</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-star fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         @else
           <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Đánh giá TB</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">N/A</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-star fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         @endisset
    </div>

    <!-- Row 2: Biểu đồ chính và Thông tin phụ -->
     <div class="row">
            <!-- Cột trái: Biểu đồ Giờ làm 7 ngày -->
             <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-info">Giờ làm trong 7 ngày qua</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height: 320px;">
                            <canvas id="workHoursChart7Days"></canvas>
                        </div>
                    </div>
                </div>
             </div>

             <!-- Cột phải: Điểm danh hôm nay & Nhận xét -->
             <div class="col-xl-4 col-lg-5">
                 <!-- Card: Điểm danh hôm nay -->
                 <div class="card shadow mb-4">
                     <div class="card-header py-3">
                         <h6 class="m-0 font-weight-bold text-primary">Điểm danh hôm nay</h6>
                     </div>
                     <div class="card-body">
                         @if($todayAttendance)
                             <p class="mb-1"><i class="fas fa-sign-in-alt text-success fa-fw mr-2"></i><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i d/m/Y') }}</p>
                             @if($todayAttendance->check_out_time)
                                 <p class="mb-1"><i class="fas fa-sign-out-alt text-danger fa-fw mr-2"></i><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i d/m/Y') }}</p>
                                 <p class="mb-1"><i class="fas fa-hourglass-half fa-fw mr-2"></i><strong>Thời gian làm:</strong> {{ round($todayAttendance->work_duration_in_hours ?? 0, 2) }} giờ</p>
                             @else
                                  <p class="mb-1"><i class="fas fa-sign-out-alt text-muted fa-fw mr-2"></i><strong>Check-out:</strong> Chưa check-out</p>
                             @endif
                              <p class="mb-2"><i class="fas fa-clipboard-check fa-fw mr-2"></i><strong>Trạng thái:</strong>
                                 @if($todayAttendance->status == 'on_time') {{-- Giả sử bạn có status 'on_time' --}}
                                     <span class="badge bg-success text-white">Đúng giờ</span>
                                 @elseif($todayAttendance->status == 'late')
                                     <span class="badge bg-warning text-dark">Muộn</span>
                                 @else
                                     {{-- Hiển thị status_text từ accessor nếu có, hoặc giá trị status --}}
                                     <span class="badge bg-secondary text-white">{{ $todayAttendance->status_text ?? ($todayAttendance->status ? Str::title(str_replace('_', ' ', $todayAttendance->status)) : 'Không rõ') }}</span>
                                 @endif
                             </p>
                             <hr class="my-3">
                         @else
                             <p class="text-muted small mb-3"><em>Bạn chưa điểm danh hôm nay.</em></p>
                         @endif

                         <div class="mt-2">
                             @if(!$todayAttendance)
                                 <form method="POST" action="{{ route('student.attendance.checkin') }}" id="checkin-form">
                                     @csrf
                                     <button type="submit" class="btn btn-success btn-icon-split w-100">
                                         <span class="icon text-white-50"><i class="fas fa-sign-in-alt"></i></span>
                                         <span class="text">Check-in ngay</span>
                                     </button>
                                 </form>
                             @elseif($todayAttendance && !$todayAttendance->check_out_time)
                                 <form method="POST" action="{{ route('student.attendance.checkout') }}" id="checkout-form">
                                      @csrf
                                      <button type="submit" class="btn btn-danger btn-icon-split w-100">
                                           <span class="icon text-white-50"><i class="fas fa-sign-out-alt"></i></span>
                                           <span class="text">Check-out ngay</span>
                                      </button>
                                  </form>
                             @else
                                 <div class="alert alert-light text-center small py-2 mb-0" role="alert">
                                     <i class="fas fa-check-circle text-success"></i> Đã hoàn thành điểm danh hôm nay.
                                 </div>
                             @endif
                         </div>
                     </div>
                 </div>

                 <!-- Card: Nhận xét gần đây -->
                 <div class="card shadow mb-4">
                     <div class="card-header py-3">
                         <h6 class="m-0 font-weight-bold text-warning">Nhận xét gần đây</h6>
                     </div>
                     <div class="card-body">
                         @if($latestReview)
                             <div class="d-flex align-items-center mb-2">
                                 <img src="{{ $latestReview->reviewer->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Reviewer Avatar" class="rounded-circle me-2" style="width: 30px; height: 30px;">
                                 <span class="font-weight-bold">{{ $latestReview->reviewer->name ?? 'N/A' }}</span>
                             </div>
                             <p class="mb-1"><strong>Nội dung:</strong></p>
                             <p class="text-muted small mb-2">{{ $latestReview->comment ?? '...' }}</p>
                             <p class="text-muted small mb-0"><i class="fas fa-clock fa-fw mr-1"></i>{{ $latestReview->created_at->diffForHumans() }}</p>
                         @else
                             <p class="text-muted small mb-0"><em>Chưa có nhận xét nào.</em></p>
                         @endif
                     </div>
                 </div>
             </div>
      </div>

    <!-- Row 3: Biểu đồ thống kê 30 ngày -->
    <div class="row">
        <!-- Biểu đồ Điểm danh (30 ngày) -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê Điểm danh (30 ngày qua)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 250px;">
                        <canvas id="studentAttendanceChart"></canvas>
                    </div>
                     <div class="mt-2 text-center small">
                        <span class="me-2"><i class="fas fa-circle text-primary"></i> Có điểm danh</span>
                        <span class="me-2"><i class="fas fa-circle" style="color: #f8f9fc;"></i> Không điểm danh</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ Nhật ký (30 ngày) -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thống kê Nộp Nhật ký (30 ngày qua)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-line" style="height: 250px;">
                        <canvas id="studentDiaryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Đảm bảo Chart.js được nhúng --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Script vẽ biểu đồ và xử lý thông báo/nút bấm --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.color = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) { number = (number + '').replace(',', '').replace(' ', ''); var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, dec = (typeof dec_point === 'undefined') ? '.' : dec_point, s = '', toFixedFix = function(n, prec) { var k = Math.pow(10, prec); return '' + Math.round(n * k) / k; }; s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.'); if (s[0].length > 3) { s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep); } if ((s[1] || '').length < prec) { s[1] = s[1] || ''; s[1] += new Array(prec - s[1].length + 1).join('0'); } return s.join(dec); }

        // 1. Biểu đồ Điểm danh (30 ngày)
        var ctxAttendance = document.getElementById("studentAttendanceChart"); if (ctxAttendance) { var attendanceChartData = @json($attendanceChart ?? ['labels' => [], 'data' => []]); new Chart(ctxAttendance, { type: 'bar', data: { labels: attendanceChartData.labels, datasets: [{ label: "Điểm danh", backgroundColor: attendanceChartData.data.map(d => d > 0 ? '#4e73df' : '#f8f9fc'), borderColor: attendanceChartData.data.map(d => d > 0 ? '#4e73df' : '#e3e6f0'), data: attendanceChartData.data.map(d => d > 0 ? 1 : 0.1), categoryPercentage: 0.7, barPercentage: 0.8 }] }, options: { maintainAspectRatio: false, responsive: true, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { grid: { display: false, drawBorder: false }, ticks: { autoSkip: true, maxTicksLimit: 10 } }, y: { display: false, ticks: { min: 0, max: 1 } } }, } }); }
        // 2. Biểu đồ Nhật ký (30 ngày)
        var ctxDiary = document.getElementById("studentDiaryChart"); if (ctxDiary) { var diaryChartData = @json($diaryChart ?? ['labels' => [], 'data' => []]); new Chart(ctxDiary, { type: 'line', data: { labels: diaryChartData.labels, datasets: [{ label: "Số nhật ký", lineTension: 0.3, backgroundColor: "rgba(28, 200, 138, 0.05)", borderColor: "rgba(28, 200, 138, 1)", pointRadius: 3, pointBackgroundColor: "rgba(28, 200, 138, 1)", pointBorderColor: "rgba(28, 200, 138, 1)", pointHoverRadius: 3, pointHoverBackgroundColor: "rgba(28, 200, 138, 1)", pointHoverBorderColor: "rgba(28, 200, 138, 1)", pointHitRadius: 10, pointBorderWidth: 2, data: diaryChartData.data, }], }, options: { maintainAspectRatio: false, responsive: true, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false, callbacks: { label: function(context) { return 'Nhật ký: ' + number_format(context.parsed.y); } } } }, scales: { x: { grid: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 10 } }, y: { ticks: { maxTicksLimit: 5, padding: 10, precision: 0, callback: function(value) { if(Number.isInteger(value)) {return number_format(value);} } }, grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } } }, } }); }
        // 3. Biểu đồ Giờ làm (7 ngày)
        var ctxWorkHours = document.getElementById("workHoursChart7Days"); if (ctxWorkHours) { var workHoursData = { labels: @json($chartLabels7Days ?? []), datasets: [{ label: "Giờ làm", lineTension: 0.3, backgroundColor: "rgba(54, 185, 204, 0.05)", borderColor: "rgba(54, 185, 204, 1)", pointRadius: 3, pointBackgroundColor: "rgba(54, 185, 204, 1)", pointBorderColor: "rgba(54, 185, 204, 1)", pointHoverRadius: 3, pointHoverBackgroundColor: "rgba(54, 185, 204, 1)", pointHoverBorderColor: "rgba(54, 185, 204, 1)", pointHitRadius: 10, pointBorderWidth: 2, data: @json($chartDataHours7Days ?? []), }], }; new Chart(ctxWorkHours, { type: 'line', data: workHoursData, options: { maintainAspectRatio: false, responsive: true, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false, callbacks: { label: function(context) { return 'Giờ: ' + number_format(context.parsed.y, 2); } } } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 7 } }, y: { ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return number_format(value, 1); } }, grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } } }, } }); }

        const successAlert = document.getElementById('success-alert');
        if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
        const errorAlert = document.getElementById('error-alert');
        if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }

        const checkinForm = document.getElementById('checkin-form'); const checkoutForm = document.getElementById('checkout-form');
        if(checkinForm) { checkinForm.addEventListener('submit', function() { const btn = checkinForm.querySelector('button[type="submit"]'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang xử lý...</span>'; }); }
        if(checkoutForm) { checkoutForm.addEventListener('submit', function() { const btn = checkoutForm.querySelector('button[type="submit"]'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang xử lý...</span>'; }); }
    });
</script>
@endpush