// resources/js/notification-handler.js

// Chúng ta giả định rằng jQuery ($) đã được import và gán vào window
// trong file app.js TRƯỚC KHI file này được import.

$(document).ready(function() {
    console.log('LOG A: notification-handler.js - Document is ready. Attaching click listener for .mark-all-as-read-btn');

    // Thiết lập CSRF token cho tất cả các AJAX request của jQuery
    // Điều này yêu cầu thẻ <meta name="csrf-token" content="..."> phải có trong layout chính
    // và jQuery ($) phải có sẵn.
    if (typeof $ !== 'undefined' && typeof $.ajaxSetup === 'function') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        console.log('LOG A.1: notification-handler.js - CSRF token setup for AJAX complete.');
    } else {
        console.error('LOG A.2: notification-handler.js - jQuery or $.ajaxSetup is not available. CSRF token NOT set for AJAX.');
        // Nếu jQuery không có sẵn ở đây, thì toàn bộ logic bên dưới sẽ không hoạt động.
        // Điều này KHÔNG NÊN xảy ra nếu app.js đã import và gán jQuery vào window đúng cách.
        return;
    }

    // Sử dụng event delegation cho nút ".mark-all-as-read-btn"
    // Điều này đảm bảo rằng ngay cả khi nút được thêm vào DOM sau này, sự kiện vẫn hoạt động.
    $(document).on('click', '.mark-all-as-read-btn', function(e) {
        e.preventDefault(); // Ngăn hành động mặc định của thẻ <a> (ví dụ: điều hướng đến #)
        console.log('LOG B: Nút ".mark-all-as-read-btn" được nhấn.');

        var $button = $(this); // Lưu lại tham chiếu đến phần tử jQuery của nút đã được click
        console.log('LOG C: Nút jQuery object:', $button);
        console.log('LOG D: HTML của nút:', $button.html()); // Nội dung HTML hiện tại của nút

        // Lấy URL từ thuộc tính data-mark-all-url của nút
        var markAllUrl = $button.data('mark-all-url');
        console.log('LOG E: data-mark-all-url từ nút:', markAllUrl);

        var originalButtonText = $button.html(); // Lưu lại nội dung HTML ban đầu của nút

        // Kiểm tra xem URL có hợp lệ không
        if (!markAllUrl || markAllUrl.trim() === '' || markAllUrl === '#') {
            console.error('LOG F: Lỗi - URL không hợp lệ hoặc không được tìm thấy. URL:', markAllUrl, '. Sẽ không gửi AJAX.');
            alert('Lỗi: Không tìm thấy URL hợp lệ để đánh dấu đã đọc.');
            return; // Dừng thực thi nếu URL không hợp lệ
        }

        console.log('LOG G: Chuẩn bị gửi AJAX request đến:', markAllUrl);
        $.ajax({
            url: markAllUrl,
            type: 'POST', // Sử dụng POST cho hành động thay đổi dữ liệu trên server
            beforeSend: function() {
                console.log('LOG H: AJAX beforeSend - Đang gửi request...');
                // Thay đổi text và vô hiệu hóa nút để người dùng biết đang có xử lý
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
            },
            success: function(response) {
                console.log('LOG I: AJAX success - Phản hồi từ server:', response);

                // Cập nhật UI sau khi thành công
                var $unreadCountBadge = $('#unreadNotificationsCount');
                if ($unreadCountBadge.length) {
                    $unreadCountBadge.text('0').hide();
                    console.log('LOG I.1: Badge đếm thông báo đã được cập nhật và ẩn đi.');
                } else {
                    console.warn('LOG I.2: Không tìm thấy badge đếm thông báo với ID "unreadNotificationsCount".');
                }

                $button.hide(); // Ẩn nút "Đánh dấu tất cả đã đọc"
                console.log('LOG I.3: Nút "Đánh dấu tất cả đã đọc" đã được ẩn.');

                // Cập nhật giao diện cho từng thông báo trong dropdown
                var $notificationItems = $('#notificationsDropdownContainer .notification-item');
                console.log('LOG I.4: Tìm thấy', $notificationItems.length, 'thông báo item để cập nhật.');
                $notificationItems.each(function() {
                    var $notificationItem = $(this);
                    // Bỏ in đậm tiêu đề và đổi màu text
                    $notificationItem.find('div > span.fw-bold').removeClass('fw-bold').addClass('text-muted');

                    // Thay đổi màu nền của icon
                    var $iconCircle = $notificationItem.find('.icon-circle');
                    if ($iconCircle.hasClass('bg-primary')) { // Giả sử bg-primary là cho chưa đọc
                        $iconCircle.removeClass('bg-primary').addClass('bg-secondary'); // Giả sử bg-secondary là cho đã đọc
                    }
                    // Bạn có thể cần logic phức tạp hơn ở đây nếu có nhiều class màu khác nhau
                });

                // Tùy chọn: Hiển thị thông báo thành công cho người dùng (ví dụ: sử dụng thư viện Toastr)
                if (response && response.message) {
                     // Ví dụ: toastr.success(response.message); // Nếu bạn dùng Toastr
                     console.log('Thông báo từ server (thành công):', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('LOG J: AJAX error - Phản hồi từ server:', xhr.responseText);
                console.error('LOG J.1: AJAX error - Status:', status, 'Error thrown:', error);
                alert('Đã có lỗi xảy ra khi đánh dấu đã đọc tất cả thông báo. Vui lòng kiểm tra console để biết thêm chi tiết.');

                // Quan trọng: Khôi phục lại nút nếu có lỗi, đảm bảo nó hiển thị và có thể click lại
                $button.prop('disabled', false).html(originalButtonText).show();
                console.log('LOG J.2: Nút đã được khôi phục lại trạng thái ban đầu do lỗi AJAX.');
            }
            // complete: function() {
            //     // Hàm này sẽ chạy sau success hoặc error
            //     // Thường dùng để bỏ trạng thái loading nếu không xử lý riêng trong success/error
            //     // Trong trường hợp này, success/error đã xử lý trạng thái nút nên không cần thiết lắm.
            //     console.log('LOG K: AJAX complete - Request đã hoàn thành (dù thành công hay thất bại).');
            // }
        });
    });

    console.log('LOG L: notification-handler.js - Tất cả event listeners trong file này đã được thiết lập.');
});