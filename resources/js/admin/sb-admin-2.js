// resources/js/admin/sb-admin-2.js

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// ===== BẮT ĐẦU PHẦN THÊM MỚI CHO MOMENT.JS =====
import moment from 'moment';
// Import locale tiếng Việt (quan trọng nếu script ở view dùng moment.locale('vi'))
import 'moment/locale/vi'; // Đường dẫn này đúng cho moment v2.x
moment.locale('vi');      // Thiết lập locale tiếng Việt làm mặc định cho Moment.js

// Gán moment vào window để các script inline (trong @push('scripts')) có thể truy cập
window.moment = moment;
// ===== KẾT THÚC PHẦN THÊM MỚI CHO MOMENT.JS =====

import 'jquery.easing'; // Vẫn giữ lại nếu scroll-to-top dùng jQuery easing

// === PHẦN CODE ĐƯỢC CẬP NHẬT CHO BOOTSTRAP 5 ===
(function($) { // $ ở đây là jQuery
  "use strict";

  // 1. Toggle the side navigation (Collapse)
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    const sidebar = $(".sidebar");
    sidebar.toggleClass("toggled");

    if (sidebar.hasClass("toggled")) {
      document.querySelectorAll('.sidebar .collapse').forEach(function(collapseEl) {
        // Sử dụng window.bootstrap để chắc chắn lấy đúng instance Collapse của Bootstrap 5
        const collapseInstance = window.bootstrap.Collapse.getOrCreateInstance(collapseEl);
        collapseInstance.hide();
      });
    }
  });

  // 2. Close any open menu accordions when window is resized below 768px (Collapse)
  $(window).resize(function() {
    if ($(window).width() < 768) {
      document.querySelectorAll('.sidebar .collapse').forEach(function(collapseEl) {
        const collapseInstance = window.bootstrap.Collapse.getOrCreateInstance(collapseEl);
        collapseInstance.hide();
      });
    }

    // Force sidebar toggle
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
      document.querySelectorAll('.sidebar .collapse').forEach(function(collapseEl) {
        const collapseInstance = window.bootstrap.Collapse.getOrCreateInstance(collapseEl);
        collapseInstance.hide();
      });
    }
  });

  // 3. Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // 4. Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // 5. Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    var targetHref = $anchor.attr('href');
    var targetElement = $(targetHref);

    if (targetElement.length) {
        if (typeof $.easing !== 'undefined' && typeof $.easing.easeInOutExpo === 'function') {
            $('html, body').stop().animate({
            scrollTop: targetElement.offset().top
            }, 1000, 'easeInOutExpo');
        } else {
            $('html, body').stop().animate({
            scrollTop: targetElement.offset().top
            }, 1000);
        }
    }
    e.preventDefault();
  });

  // Khởi tạo Tooltips nếu có (sử dụng API Bootstrap 5)
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new window.bootstrap.Tooltip(tooltipTriggerEl) // Dùng window.bootstrap
  })

  // Khởi tạo Popovers nếu có (sử dụng API Bootstrap 5)
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new window.bootstrap.Popover(popoverTriggerEl) // Dùng window.bootstrap
  })

})(jQuery);
// === KẾT THÚC PHẦN CODE ĐƯỢC CẬP NHẬT ===

// =========================================================================
// PHẦN DISPATCH EVENT KHI JQUERY ĐÃ SẴN SÀNG
// =========================================================================
setTimeout(() => {
    document.dispatchEvent(new CustomEvent('jqueryLoaded', { bubbles: true }));
    console.log('sb-admin-2.js: jqueryLoaded event dispatched (after timeout).');
}, 0);
// =========================================================================

// === PHẦN CODE THÊM MỚI CỦA BẠN ===
document.addEventListener('DOMContentLoaded', function() {
    console.log('Global JS (sb-admin-2.js) Loaded and DOM Ready!'); // Đổi tên log để rõ ràng hơn

    // Auto-hide flash alerts (Sử dụng API Alert của Bootstrap 5)
    const autoDismissAlerts = document.querySelectorAll('.alert-dismissible[role="alert"][data-auto-dismiss]');
    autoDismissAlerts.forEach(function(alertEl) {
        const timeout = parseInt(alertEl.dataset.autoDismiss, 10) || 5000;
        setTimeout(function() {
            // Sử dụng window.bootstrap để chắc chắn lấy đúng instance Alert của Bootstrap 5
            const alertInstance = window.bootstrap.Alert.getInstance(alertEl);
            if (alertInstance) {
                alertInstance.close();
            } else {
                // Fallback nếu không tìm thấy instance
                const closeButton = alertEl.querySelector('.btn-close') || alertEl.querySelector('.close');
                if (closeButton) {
                    closeButton.click();
                } else {
                    alertEl.style.display = 'none';
                }
            }
        }, timeout);
    });
    // ... (phần xử lý notification bằng Fetch API của bạn giữ nguyên) ...
});
// === KẾT THÚC PHẦN THÊM MỚI CỦA BẠN ===