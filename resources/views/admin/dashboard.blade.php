@extends('layouts.admin') {{-- Hoặc file layout admin chính của bạn --}}

@section('title', __('Bảng điều khiển Admin')) {{-- Sử dụng __() cho đa ngôn ngữ --}}

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Tăng kích thước tiêu đề trang --}}
        <h1 class="h2 mb-0 text-gray-900 fw-bold">@yield('title')</h1> {{-- Tăng độ đậm, màu đen --}}
        {{-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a> --}}
    </div>

    <!-- Content Row: Summary Cards -->
    <div class="row">

        <!-- Card: Tổng số Sinh viên -->
        <div class="col-xl-3 col-md-6 mb-4">
             {{-- Thêm transition cho hover effect --}}
            <div class="card border-left-primary shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Sinh viên (Tổng số)') }}</div>
                             {{-- Tăng kích thước số liệu --}}
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-3x text-gray-300"></i> {{-- Tăng kích thước icon --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Lịch đang hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Lịch đang hoạt động') }}</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['active_schedules'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-3x text-gray-300"></i> {{-- Icon khác & lớn hơn --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Yêu cầu Lịch (Chờ duyệt) -->
         <div class="col-xl-3 col-md-6 mb-4">
             <div class="card border-left-info shadow h-100 py-2 card-hover">
                 <div class="card-body">
                     <div class="row no-gutters align-items-center">
                         <div class="col mr-2">
                             <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                 {{ __('Yêu cầu Lịch (Chờ duyệt)') }}</div>
                             <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_schedule_requests'] ?? 0 }}</div>
                         </div>
                         <div class="col-auto">
                             <i class="fas fa-inbox fa-3x text-gray-300"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>

        <!-- Card: Nhật ký (Chờ xem) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Nhật ký (Chờ xem)') }}</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_diaries'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comment-dots fa-3x text-gray-300"></i> {{-- Icon khác & lớn hơn --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row: Charts and Activity/Alerts -->
    <div class="row">

        <!-- Area Chart Column -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3">
                    {{-- Tiêu đề rõ ràng hơn --}}
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Thống kê hoạt động (7 ngày gần nhất)') }}</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                     {{-- Thêm padding cho chart container --}}
                    <div class="chart-area p-2" style="height: 320px;">
                        <canvas id="adminActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Row for Pie and Bar Charts -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100"> {{-- Thêm h-100 để các card cao bằng nhau --}}
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('Phân bổ Sinh viên theo Ngành') }}</h6>
                        </div>
                        <div class="card-body d-flex flex-column"> {{-- Flex để legend đẩy xuống dưới --}}
                             {{-- Thêm padding --}}
                            <div class="chart-pie pt-2 pb-2 flex-grow-1" style="min-height: 200px;">
                                <canvas id="adminMajorChart"></canvas>
                            </div>
                             {{-- Sử dụng legend của Chart.js (đã cấu hình trong JS) thay vì div tĩnh --}}
                            {{-- <div class="mt-4 text-center small" id="adminMajorChartLegend"></div> --}}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                     <div class="card shadow h-100"> {{-- Thêm h-100 --}}
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('Tiến độ Thực tập (Tổng quan)') }}</h6>
                        </div>
                        <div class="card-body">
                             {{-- Thêm padding --}}
                            <div class="chart-bar p-2" style="height: 265px;">
                                <canvas id="adminProgressChart"></canvas>
                            </div>
                         </div>
                     </div>
                </div>
            </div>

        </div>

        <!-- Activity Feed & Alerts Column -->
        <div class="col-xl-4 col-lg-5">
            <!-- Recent Activity Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Hoạt động gần đây') }}</h6>
                </div>
                {{-- Sử dụng list-group để đẹp hơn --}}
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentActivities ?? [] as $activity) {{-- Thêm default [] --}}
                        <a href="{{ $activity['link'] ?? '#' }}" class="list-group-item list-group-item-action flex-column align-items-start py-3">
                            <div class="d-flex w-100 justify-content-between mb-1">
                                <div class="text-gray-800"> {{-- Tăng độ đậm chữ --}}
                                    <i class="{{ $activity['icon'] ?? 'fas fa-info-circle' }} fa-fw me-2 text-gray-400"></i> {{-- Thêm màu và margin --}}
                                    {!! $activity['text'] ?? '' !!} {{-- Thêm default '' --}}
                                </div>
                                <small class="text-nowrap text-muted">{{ isset($activity['time']) ? \Carbon\Carbon::parse($activity['time'])->diffForHumans(null, true, true) : '' }}</small> {{-- Ngắn gọn hơn, kiểm tra isset --}}
                            </div>
                            {{-- <small class="text-muted">Optional details here</small> --}}
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted small py-3">
                            <em>{{ __('Chưa có hoạt động nào gần đây.') }}</em>
                        </div>
                    @endforelse
                </div>
                 <div class="card-footer text-center">
                    <a href="#" class="small text-decoration-none"> {{-- Bỏ gạch chân --}}
                        {{ __('Xem tất cả hoạt động') }} <i class="fas fa-arrow-right fa-sm"></i>
                    </a>
                 </div>
            </div>

            <!-- Alerts Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning border-0"> {{-- Nền warning cho header --}}
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-exclamation-triangle me-2"></i>{{ __('Cảnh báo quan trọng') }}</h6>
                </div>
                 {{-- Sử dụng list-group --}}
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                     @forelse($alerts ?? [] as $alert) {{-- Thêm default [] --}}
                        <a href="{{ $alert['link'] ?? '#' }}" class="list-group-item list-group-item-action flex-column align-items-start py-3 list-group-item-warning"> {{-- Màu nền warning nhẹ --}}
                            <div class="d-flex w-100">
                                <div class="me-2 pt-1">
                                    <i class="{{ $alert['icon'] ?? 'fas fa-bell' }} fa-fw text-warning"></i> {{-- Icon màu vàng --}}
                                </div>
                                <div class="text-gray-900 small"> {{-- Chữ đen dễ đọc --}}
                                     {!! $alert['text'] ?? '' !!} {{-- Thêm default '' --}}
                                </div>
                            </div>
                        </a>
                     @empty
                         <div class="list-group-item text-center text-muted small py-3">
                             <em>{{ __('Không có cảnh báo nào.') }}</em>
                         </div>
                     @endforelse
                </div>
                  <div class="card-footer text-center">
                      <a href="#" class="small text-decoration-none">
                          {{ __('Xem tất cả cảnh báo') }} <i class="fas fa-arrow-right fa-sm"></i>
                      </a>
                  </div>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- Push CSS tùy chỉnh --}}
@push('styles')
<style>
    /* 1. Sidebar Active State */
    /* Giả sử sidebar của bạn có cấu trúc ul#accordionSidebar > li.nav-item */
    #accordionSidebar .nav-item.active > .nav-link {
        background-color: rgba(255, 255, 255, 0.1); /* Màu nền nhẹ */
        /* Hoặc thêm border */
         border-left: 3px solid #fff; /* Màu trắng hoặc màu chính */
         font-weight: 600; /* Chữ đậm hơn */
         /* margin-left: -3px; Đẩy nhẹ sang trái để border không làm lệch text */
    }
    #accordionSidebar .nav-item.active > .nav-link i {
        /* color: #fff; */ /* Có thể đổi màu icon nếu muốn */
    }

    /* 2. Card Hover Effect */
    .card-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-hover:hover {
        transform: translateY(-5px); /* Nhấc nhẹ card lên */
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; /* Tăng shadow */
    }

    /* 3. Activity Feed spacing (List Group đã có khoảng cách tốt) */
    /* .list-group-item { padding-top: 1rem; padding-bottom: 1rem; } */ /* Tăng padding nếu muốn */

    /* 4. Chart Padding (Đã thêm class p-2 vào container chart) */
    .chart-area, .chart-pie, .chart-bar {
        position: relative; /* Cần thiết cho Chart.js */
    }

    /* 5. Tăng độ đậm chữ activity feed */
     .list-group-item .text-gray-800 {
         color: #5a5c69 !important; /* Màu đậm hơn chút */
         font-weight: 500;
     }

</style>
@endpush


@push('scripts')
    {{-- Đảm bảo Chart.js đã được nhúng --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script> {{-- Sử dụng Chart.js v3 nếu có thể --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Cấu hình chung cho Chart.js v3 ---
            Chart.defaults.font.family = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.color = '#858796';
            Chart.defaults.plugins.tooltip.backgroundColor = "rgb(255,255,255)";
            Chart.defaults.plugins.tooltip.bodyColor = "#858796";
            Chart.defaults.plugins.tooltip.borderColor = '#dddfeb';
            Chart.defaults.plugins.tooltip.borderWidth = 1;
            Chart.defaults.plugins.tooltip.padding = 15; // Thống nhất padding tooltip
            Chart.defaults.plugins.tooltip.displayColors = false;
            Chart.defaults.plugins.tooltip.intersect = false;
            Chart.defaults.plugins.tooltip.mode = 'index';

            function number_format(number, decimals, dec_point, thousands_sep) {
              number = (number + '').replace(',', '').replace(' ', '');
              var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                  var k = Math.pow(10, prec);
                  return '' + Math.round(n * k) / k;
                };
              s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
              if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
              }
              if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
              }
              return s.join(dec);
            }

            // --- 1. Activity Area Chart ---
            var ctxActivity = document.getElementById("adminActivityChart");
            if (ctxActivity) {
                var activityChartData = @json($activityChartData ?? ['labels' => [], 'datasets' => []]);
                var adminActivityChart = new Chart(ctxActivity.getContext('2d'), { // Dùng getContext('2d')
                    type: 'line',
                    data: activityChartData, // Đã chuẩn bị sẵn data từ Controller
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        interaction: { // Cho Chart.js v3
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                grid: { display: false, drawBorder: false }, // Grid X ẩn
                                ticks: { maxTicksLimit: 7, color: '#858796' } // Màu ticks
                            },
                            y: {
                                ticks: {
                                    maxTicksLimit: 5,
                                    padding: 10,
                                    beginAtZero: true,
                                    color: '#858796',
                                    callback: function(value) { if (Number.isInteger(value)) { return value; } }
                                },
                                grid: { // Hiện Grid Y
                                    display: true,
                                    color: "rgba(221, 223, 235, 0.5)", // Nhạt hơn chút
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    zeroLineBorderDash: [2]
                                }
                            },
                        },
                        plugins: {
                             legend: { display: true, position: 'bottom', labels: { color: '#5a5c69'} }, // Legend rõ hơn
                             tooltip: { // Tooltip đã được cấu hình bởi defaults
                                 titleColor: '#6e707e',
                                 callbacks: {
                                     label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) { label += ': '; }
                                        if (context.parsed.y !== null) {
                                            label += number_format(context.parsed.y);
                                        }
                                        return label;
                                     }
                                 }
                             }
                        },
                        elements: { // Tùy chỉnh style đường line và điểm
                            line: {
                                tension: 0.3 // Làm mượt đường line
                            },
                            point:{
                                radius: 3,
                                hoverRadius: 4,
                            }
                        }
                    }
                });
            }

            // --- 2. Major Pie/Doughnut Chart ---
            var ctxMajor = document.getElementById("adminMajorChart");
             if (ctxMajor) {
                {{-- === FIX LỖI PARSE ERROR === --}}
                @php
                    $defaultMajorData = [
                        'labels' => [],
                        'datasets' => [
                            [ // Mảng chứa object dataset đầu tiên
                                'data' => [],
                                'backgroundColor' => [],
                                'hoverBackgroundColor' => []
                            ]
                        ]
                    ];
                    // Đảm bảo $majorChartData luôn là một mảng có cấu trúc đúng trước khi encode
                    $chartDataForJson = $majorChartData ?? $defaultMajorData;
                    // Kiểm tra thêm cấu trúc datasets nếu $majorChartData không null
                    if (isset($majorChartData['datasets']) && (!isset($majorChartData['datasets'][0]) || !is_array($majorChartData['datasets'][0]))) {
                        // Nếu cấu trúc datasets không đúng, sử dụng default
                         $chartDataForJson['datasets'] = $defaultMajorData['datasets'];
                    } elseif (!isset($majorChartData['datasets'])) {
                        $chartDataForJson['datasets'] = $defaultMajorData['datasets'];
                    }
                @endphp
                {{-- Truyền biến đã chuẩn bị vào @json --}}
                var majorChartData = @json($chartDataForJson);
                 {{-- === KẾT THÚC FIX === --}}

                 var adminMajorChart = new Chart(ctxMajor.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: majorChartData.labels,
                        datasets: majorChartData.datasets, // Dữ liệu đã có cấu trúc đúng
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true, // Hiện legend
                                position: 'bottom', // Đặt legend dưới
                                labels: {
                                    padding: 20, // Tăng khoảng cách legend
                                    color: '#5a5c69', // Màu chữ legend
                                    boxWidth: 12,
                                    usePointStyle: true, // Dùng kiểu điểm tròn
                                }
                            },
                            tooltip: { // Đã cấu hình bởi defaults
                                callbacks: {
                                     label: function(context) {
                                        let label = context.label || '';
                                        if (label) { label += ': '; }
                                        if (context.parsed !== null) {
                                             label += number_format(context.parsed); // Chỉ hiển thị số lượng
                                        }
                                        return label;
                                    }
                                }
                            },
                        },
                        cutout: '75%' // Tăng độ mỏng doughnut
                    },
                });
             }

             // --- 3. Progress Bar Chart ---
             var ctxProgress = document.getElementById("adminProgressChart");
             if (ctxProgress) {
                 var progressChartData = @json($progressChartData ?? ['labels' => [], 'datasets' => []]);
                 var adminProgressChart = new Chart(ctxProgress.getContext('2d'), {
                    type: 'bar',
                    data: progressChartData,
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                             legend: { display: false }, // Vẫn ẩn legend
                             tooltip: { // Đã cấu hình bởi defaults
                                 titleColor: '#6e707e',
                                 callbacks: {
                                     label: function(context) {
                                         return 'Số SV: ' + number_format(context.parsed.y);
                                     }
                                 },
                             },
                        },
                        scales: {
                             x: {
                                 grid: { display: false, drawBorder: false },
                                 ticks: { maxTicksLimit: 6, color: '#858796' }
                             },
                             y: {
                                 ticks: {
                                     min: 0,
                                     maxTicksLimit: 5,
                                     padding: 10,
                                     color: '#858796',
                                     callback: function(value) { if (Number.isInteger(value)) { return number_format(value); } }
                                 },
                                 grid: { // Hiện Grid Y
                                     display: true,
                                     color: "rgba(221, 223, 235, 0.5)", // Nhạt hơn
                                     drawBorder: false,
                                     borderDash: [2],
                                     zeroLineColor: "rgb(234, 236, 244)",
                                     zeroLineBorderDash: [2]
                                 }
                             },
                         },
                         // indexAxis: 'y', // Bỏ comment nếu muốn biểu đồ cột ngang
                         elements: {
                             bar: {
                                 borderRadius: 4, // Bo tròn góc cột
                                 borderSkipped: false // Bo tròn cả 4 góc (nếu muốn)
                             }
                         }
                    }
                 });
             }

        });
    </script>
@endpush