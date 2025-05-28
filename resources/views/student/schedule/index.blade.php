{{-- resources/views/student/schedule/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Lịch Thực Tập Của Bạn')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Lịch trình thực tập của bạn</h1>
    </div>

    {{-- PHẦN CHỌN TUẦN --}}
    <div class="row align-items-end mb-3 gx-2">
        <div class="col-md-auto">
            <label for="yearSelector" class="form-label mb-1">Năm:</label>
            <select class="form-select form-select-sm" id="yearSelector" style="min-width: 100px;">
                @php
                    $currentSelectedYear = isset($referenceDate) ? $referenceDate->year : now()->year;
                    $startYearOption = $currentSelectedYear - 2;
                    $endYearOption = $currentSelectedYear + 2;
                @endphp
                @for ($yearLoopVal = $startYearOption; $yearLoopVal <= $endYearOption; $yearLoopVal++)
                    <option value="{{ $yearLoopVal }}" {{ $yearLoopVal == $currentSelectedYear ? 'selected' : '' }}>
                        {{ $yearLoopVal }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-auto">
            <label for="weekSelector" class="form-label mb-1">Tuần:</label>
            <select class="form-select form-select-sm" id="weekSelector" style="min-width: 120px;">
                {{-- JavaScript sẽ điền các option tuần vào đây --}}
            </select>
        </div>
    </div>
    <div class="mb-3">
        <p id="currentWeekInfoDisplayed" class="fw-bold"></p>
    </div>
    {{-- KẾT THÚC PHẦN CHỌN TUẦN --}}


    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert" data-auto-dismiss="5000">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert" data-auto-dismiss="8000">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div id="schedulesTableContainer">
        @include('student.schedule.partials.schedules_table', ['schedules' => $schedules])
    </div>

    <div class="modal fade" id="scheduleDetailModal" tabindex="-1" aria-labelledby="scheduleDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleDetailModalLabel">Chi Tiết Lịch Thực Tập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="scheduleDetailModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tải dữ liệu chi tiết...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-select.form-select-sm {
        padding-top: .25rem;
        padding-bottom: .25rem;
        padding-left: .5rem;
        font-size: .875rem;
    }
    .form-label { margin-bottom: .25rem; }
    .fw-bold { font-weight: bold !important; }
    .timetable-modal td .slot-entry { font-size: 0.9em; }
    .timetable-modal th, .timetable-modal td { vertical-align: top!important; }
    .badge.badge-info { background-color: #17a2b8; }
    .badge.badge-warning { background-color: #ffc107; color: #212529; }
    .badge.badge-danger { background-color: #dc3545; }
    .badge.badge-success { background-color: #28a745; }
    .badge.badge-secondary { background-color: #6c757d; }
</style>
@endpush

@push('scripts')
{{-- Nhắc lại: Đảm bảo Moment.js đã được nhúng TRƯỚC khi script này chạy (thường là trong sb-admin-2.js hoặc layout chính) --}}
<script>
    document.addEventListener('jqueryLoaded', function() {
        console.log('STUDENT SCHEDULE PAGE: jqueryLoaded event received.');

        if (typeof moment === 'undefined') {
            console.error("CRITICAL ERROR: Moment.js is not loaded. Week selection functionality will not work.");
            $('#currentWeekInfoDisplayed').text("Lỗi: Thư viện ngày tháng (Moment.js) chưa tải. Vui lòng liên hệ quản trị viên.").addClass('text-danger');
            $('#yearSelector, #weekSelector').prop('disabled', true);
            return;
        }
        if (typeof moment.locale === 'function') {
            moment.locale('vi');
        }

        const yearSelector = $('#yearSelector');
        const weekSelector = $('#weekSelector');
        const schedulesTableContainer = $('#schedulesTableContainer');
        const currentWeekInfoDisplayed = $('#currentWeekInfoDisplayed');
        let userInteractedWeekChange = false; // Cờ để kiểm soát việc gọi loadSchedules khi người dùng thực sự thay đổi tuần

        function populateWeekOptions(year, selectedWeek = null) {
            weekSelector.empty();
            let defaultSelectedWeek = selectedWeek;
            if (selectedWeek === null) {
                defaultSelectedWeek = (parseInt(year) === moment().year()) ? moment().isoWeek() : 1;
            }

            const weeksInYear = moment(String(year), "YYYY").isoWeeksInYear();
            for (let i = 1; i <= weeksInYear; i++) {
                const firstDayOfWeekOption = moment().year(year).isoWeek(i).startOf('isoWeek');
                const lastDayOfWeekOption = moment().year(year).isoWeek(i).endOf('isoWeek');
                let addThisWeek = true;
                if (firstDayOfWeekOption.year() > parseInt(year) && i === 1 && weeksInYear === 53 && moment(String(parseInt(year)-1), "YYYY").isoWeeksInYear() === 53) {}
                else if ( (firstDayOfWeekOption.year() < parseInt(year) && lastDayOfWeekOption.year() < parseInt(year)) || (firstDayOfWeekOption.year() > parseInt(year) && lastDayOfWeekOption.year() > parseInt(year)) ) { addThisWeek = false; }
                if (!addThisWeek && !(firstDayOfWeekOption.year() === parseInt(year) || lastDayOfWeekOption.year() === parseInt(year))) { continue; }

                const optionText = `Tuần ${i}`;
                const optionValue = firstDayOfWeekOption.format('YYYY-MM-DD');
                weekSelector.append(new Option(optionText, optionValue));
            }

            const dateForSelectedWeek = moment().year(year).isoWeek(defaultSelectedWeek).startOf('isoWeek').format('YYYY-MM-DD');
            weekSelector.val(dateForSelectedWeek); // Set giá trị cho dropdown

            // Gọi loadSchedulesForSelectedWeek chỉ khi populate được gọi do khởi tạo hoặc thay đổi năm
            // userInteractedWeekChange sẽ được set true khi người dùng chủ động thay đổi weekSelector
            if (!userInteractedWeekChange) {
                loadSchedulesForSelectedWeek();
            }
            userInteractedWeekChange = false; // Reset cờ sau khi populate xong
        }

        function loadSchedulesForSelectedWeek() {
            const selectedDateValue = weekSelector.val();
            if (!selectedDateValue) {
                currentWeekInfoDisplayed.text('Vui lòng chọn một tuần.');
                schedulesTableContainer.html('<p class="text-center p-3">Vui lòng chọn tuần để xem lịch.</p>');
                return;
            }

            const firstDayOfWeek = moment(selectedDateValue, 'YYYY-MM-DD');
            const lastDayOfWeek = firstDayOfWeek.clone().endOf('isoWeek');
            const weekNumber = firstDayOfWeek.isoWeek();
            const year = firstDayOfWeek.year();

            currentWeekInfoDisplayed.text(`Tuần ${weekNumber} (Năm ${year}): Từ ngày ${firstDayOfWeek.format('DD/MM/YYYY')} đến ngày ${lastDayOfWeek.format('DD/MM/YYYY')}`);
            schedulesTableContainer.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Đang tải lịch...</span></div></div>');

            $.ajax({
                url: "{{ route('student.schedule.index') }}",
                type: 'GET',
                data: { date: firstDayOfWeek.format('YYYY-MM-DD'), is_ajax: 1 },
                success: function(responseHtml) { schedulesTableContainer.html(responseHtml); },
                error: function(xhr) {
                    console.error("Lỗi khi tải lịch trình:", xhr);
                    schedulesTableContainer.html('<p class="text-danger text-center p-3">Không thể tải lịch trình.</p>');
                    currentWeekInfoDisplayed.text('Lỗi khi tải thông tin tuần.');
                }
            });
        }

        // Khởi tạo khi trang tải lần đầu
        @php
            $initialLoadYear = isset($referenceDate) ? $referenceDate->year : now()->year;
            // Đảm bảo $referenceDate là đối tượng Carbon trước khi gọi copy()
            $initialLoadWeekValue = '';
            if (isset($referenceDate) && $referenceDate instanceof \Carbon\Carbon) {
                $initialLoadWeekValue = $referenceDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            } else {
                $initialLoadWeekValue = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            }
        @endphp

        yearSelector.val({{ $initialLoadYear }});
        // Truyền giá trị ngày đầu tuần YYYY-MM-DD vào populateWeekOptions để nó có thể tính ra số tuần
        populateWeekOptions({{ $initialLoadYear }}, moment('{{ $initialLoadWeekValue }}', 'YYYY-MM-DD').isoWeek());


        yearSelector.on('change', function() {
            userInteractedWeekChange = false; // Khi năm thay đổi, populateWeekOptions sẽ tự load tuần mới
            const selectedYear = parseInt($(this).val());
            const weekToSelect = (selectedYear === moment().year()) ? moment().isoWeek() : 1;
            populateWeekOptions(selectedYear, weekToSelect);
        });

        weekSelector.on('change', function() {
            userInteractedWeekChange = true; // Người dùng chủ động thay đổi tuần
            loadSchedulesForSelectedWeek();
        });

        // ---- PHẦN XỬ LÝ MODAL XEM CHI TIẾT (GIỮ NGUYÊN) ----
        $(document).on('click', '.view-schedule-detail', function() {
            var detailUrl = $(this).data('url');
            var modalBody = $('#scheduleDetailModalBody');
            var modalLabel = $('#scheduleDetailModalLabel');
            var selectedWeekStartDateForModal = $('#weekSelector').val();

            var scheduleModalElement = document.getElementById('scheduleDetailModal');
            var scheduleModalInstance = bootstrap.Modal.getInstance(scheduleModalElement);
            if (!scheduleModalInstance) { scheduleModalInstance = new bootstrap.Modal(scheduleModalElement); }

            if (!detailUrl) { modalBody.html('<p class="text-danger">Lỗi URL.</p>'); return; }
            modalBody.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Đang tải...</p></div>');
            modalLabel.text('Chi Tiết Lịch Thực Tập');

            $.ajax({
                url: detailUrl,
                type: 'GET',
                dataType: 'json',
                data: { selected_week_date: selectedWeekStartDateForModal },
                success: function(response) {
                    if (response.success && response.schedule) {
                        var scheduleData = response.schedule;
                        var daysOfWeekNames = response.daysOfWeekNames;
                        var weekInfo = response.week_info;
                        var htmlContent = '';
                        modalLabel.text('Chi Tiết: ' + scheduleData.title);
                        if (weekInfo) {
                            htmlContent += '<div class="alert alert-info text-center p-2 mb-3" role="alert" style="font-size: 0.9rem;">';
                            htmlContent += '<strong>Tuần ' + weekInfo.number + '</strong>';
                            htmlContent += ' (từ ngày ' + weekInfo.start_date_formatted;
                            htmlContent += ' đến ngày ' + weekInfo.end_date_formatted + ')';
                            htmlContent += '</div>';
                        }
                        htmlContent += '<div class="mb-3 p-3 bg-white rounded shadow-sm border">';
                        htmlContent += '<h5>' + scheduleData.title + '</h5>';
                        htmlContent += '<p class="mb-1"><strong>Mô tả:</strong> ' + (scheduleData.description || 'Không có') + '</p>';
                        htmlContent += '<p class="mb-1"><strong>Thời gian tổng thể:</strong> ' + scheduleData.overall_start_time + ' - ' + scheduleData.overall_end_time + '</p>';
                        htmlContent += '<p class="mb-1"><strong>Trạng thái:</strong> ' + scheduleData.status_text + '</p>';
                        htmlContent += '<p class="mb-0"><strong>Người tạo:</strong> ' + scheduleData.assigner_name + '</p>';
                        htmlContent += '</div><hr class="my-3">';
                        htmlContent += '<h6><strong>Thời Khóa Biểu Hàng Tuần:</strong></h6>';
                        htmlContent += '<div class="table-responsive mt-2">';
                        htmlContent += '<table class="table table-bordered table-sm timetable-modal" style="min-width: 800px;">';
                        htmlContent += '<thead class="thead-light"><tr>';
                        for (var dayNumber = 1; dayNumber <= 7; dayNumber++) { htmlContent += '<th class="text-center align-middle" style="width: 14.28%;">' + (daysOfWeekNames[dayNumber] || 'Ngày ' + dayNumber) + '</th>'; }
                        htmlContent += '</tr></thead>';
                        htmlContent += '<tbody><tr>';
                        for (var dayNumber = 1; dayNumber <= 7; dayNumber++) {
                            htmlContent += '<td class="p-2" style="vertical-align: top; min-height: 100px; max-height: 200px; overflow-y: auto;">';
                            if (scheduleData.weekly_slots && scheduleData.weekly_slots[dayNumber] && scheduleData.weekly_slots[dayNumber].length > 0) {
                                scheduleData.weekly_slots[dayNumber].forEach(function(slot) {
                                    htmlContent += '<div class="slot-entry mb-2 p-2 border rounded bg-light shadow-sm">';
                                    htmlContent += '<div class="font-weight-bold text-primary">' + slot.start_time + ' - ' + slot.end_time + '</div>';
                                    htmlContent += '<small class="text-muted d-block" style="white-space: pre-wrap; word-break: break-word;">' + slot.task + '</small>';
                                    htmlContent += '</div>';
                                });
                            } else { htmlContent += '<div class="text-center text-muted pt-4 small font-italic">-- Trống --</div>'; }
                            htmlContent += '</td>';
                        }
                        htmlContent += '</tr></tbody></table>';
                        htmlContent += '</div>';
                        modalBody.html(htmlContent);
                    } else { modalBody.html('<p class="text-center text-danger">' + (response.error || 'Không thể tải dữ liệu.') + '</p>'); }
                },
                error: function(xhr) { modalBody.html('<p class="text-danger">Lỗi AJAX.</p>'); console.error("AJAX error for detail:", xhr); }
            });
        });

        var scheduleModalElementToReset = document.getElementById('scheduleDetailModal');
        if (scheduleModalElementToReset) {
            scheduleModalElementToReset.addEventListener('hidden.bs.modal', function () {
                $('#scheduleDetailModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Đang tải...</p></div>');
                $('#scheduleDetailModalLabel').text('Chi Tiết Lịch Thực Tập');
            });
        }
    });
</script>
@endpush