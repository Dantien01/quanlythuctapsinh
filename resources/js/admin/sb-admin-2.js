// resources/js/admin/sb-admin-2.js

// === BẮT ĐẦU PHẦN THÊM MỚI: IMPORT DEPENDENCIES ===
import * as bootstrap from 'bootstrap';
import jQuery from 'jquery'; // jQuery vẫn cần cho code gốc của SB Admin 2
import 'jquery.easing';

window.$ = window.jQuery = jQuery;
window.bootstrap = bootstrap;
// === KẾT THÚC PHẦN THÊM MỚI ===


// === PHẦN CODE GỐC CỦA SB ADMIN 2 (GIỮ NGUYÊN) ===
(function($) {
  "use strict";
  // ... (toàn bộ code gốc của SB Admin 2 bạn đã cung cấp) ...
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      var collapseElementList = [].slice.call(document.querySelectorAll('.sidebar .collapse'));
      collapseElementList.forEach(function (collapseEl) {
        var collapseInstance = bootstrap.Collapse.getInstance(collapseEl);
        if (collapseInstance) {
            collapseInstance.hide();
        }
      });
    };
  });

  $(window).resize(function() {
    if ($(window).width() < 768) {
       var collapseElementList = [].slice.call(document.querySelectorAll('.sidebar .collapse'));
       collapseElementList.forEach(function (collapseEl) {
           var collapseInstance = bootstrap.Collapse.getInstance(collapseEl);
           if (collapseInstance) {
               collapseInstance.hide();
           }
       });
    };

    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
       var collapseElementList = [].slice.call(document.querySelectorAll('.sidebar .collapse'));
       collapseElementList.forEach(function (collapseEl) {
           var collapseInstance = bootstrap.Collapse.getInstance(collapseEl);
           if (collapseInstance) {
               collapseInstance.hide();
           }
       });
    };
  });

  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    var target = $anchor.attr('href');
    if (target && target.startsWith('#') && $(target).length) {
        $('html, body').stop().animate({
          scrollTop: $(target).offset().top
        }, 1000, 'easeInOutExpo');
    }
    e.preventDefault();
  });

})(jQuery);
// === KẾT THÚC PHẦN CODE GỐC ===


// === BẮT ĐẦU PHẦN CODE THÊM MỚI: XỬ LÝ THÔNG BÁO VÀ ALERT (DÙNG FETCH API) ===
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification JS Loaded and DOM Ready!'); // DEBUG LINE

    // --- Auto-hide flash alerts (CODE GỐC CỦA BẠN - GIỮ NGUYÊN) ---
    const autoDismissAlerts = document.querySelectorAll('.alert-dismissible[role="alert"]');
    autoDismissAlerts.forEach(alertElement => {
        let timeout = 5000;
        if (alertElement.classList.contains('alert-danger') || alertElement.classList.contains('alert-warning')) {
            timeout = 8000;
        }
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const alertInstance = bootstrap.Alert.getOrCreateInstance(alertElement);
                if(alertInstance) {
                    alertInstance.close();
                } else {
                    alertElement.style.display = 'none';
                }
            } else {
                alertElement.style.display = 'none';
            }
        }, timeout);
    });

    // === PHẦN XỬ LÝ NOTIFICATION BẰNG FETCH API ===
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const notificationsDropdownContainer = document.getElementById('notificationsDropdownContainer');

    function updateNotificationCount(newCount) {
        const countBadge = document.getElementById('unreadNotificationsCount');
        console.log('Fetch: Updating notification count to:', newCount); // BỎ COMMENT LOG

        if (countBadge) {
            if (newCount > 0) {
                countBadge.textContent = newCount > 9 ? '9+' : newCount;
                countBadge.style.display = '';
            } else {
                countBadge.textContent = '0';
                countBadge.style.display = 'none';
            }
        } else {
            console.error('Fetch: Notification count badge #unreadNotificationsCount not found!'); // BỎ COMMENT LOG
        }
    }

    async function sendMarkRequest(url, redirectUrl = null, isMarkAll = false, clickedItem = null) {
        if (!csrfToken) {
            console.error('CSRF token not found!');
            if (redirectUrl && redirectUrl !== '#' && !isMarkAll) window.location.href = redirectUrl;
            return;
        }

        console.log(`Fetch: Sending mark request. URL: ${url}, isMarkAll: ${isMarkAll}`); // BỎ COMMENT LOG

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            const data = await response.json();
            console.log('Fetch: Response data:', data); // BỎ COMMENT LOG

            if (!response.ok) {
                console.error(`Fetch: Network response was not ok for ${isMarkAll ? 'mark all' : 'mark one'} request.`, response.status, response.statusText, data);
                throw new Error(data.message || `Failed to ${isMarkAll ? 'mark all notifications' : 'mark notification'}`);
            }

            if (data.status === 'success') {
                console.log(`Fetch: ${isMarkAll ? 'All notifications' : 'Notification'} marked as read. New unread count:`, data.unread_count); // BỎ COMMENT LOG
                if (data.unread_count !== undefined) {
                    updateNotificationCount(data.unread_count);
                }

                if (isMarkAll && notificationsDropdownContainer) {
                    notificationsDropdownContainer.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('bg-light');
                        const textSpan = item.querySelector('span.fw-bold');
                        if (textSpan) {
                            textSpan.classList.remove('fw-bold');
                            textSpan.classList.add('text-muted');
                        }
                    });
                } else if (clickedItem) {
                    clickedItem.classList.remove('bg-light');
                    const textSpan = clickedItem.querySelector('span.fw-bold');
                    if (textSpan) {
                        textSpan.classList.remove('fw-bold');
                        textSpan.classList.add('text-muted');
                    }
                }
            } else {
                console.error(`Fetch: Failed to ${isMarkAll ? 'mark all notifications' : 'mark notification'} as read (server error):`, data.message || 'Unknown server error');
            }

        } catch (error) {
            console.error(`Fetch: Error during ${isMarkAll ? 'mark all' : 'mark one'} request:`, error);
        } finally {
            if (redirectUrl && redirectUrl !== '#' && !isMarkAll) {
                console.log('Fetch: Redirecting to:', redirectUrl); // BỎ COMMENT LOG
                window.location.href = redirectUrl;
            }
        }
    }

    if (notificationsDropdownContainer) {
        notificationsDropdownContainer.addEventListener('click', function(event) {
            const clickedLink = event.target.closest('.notification-item');
            if (clickedLink) {
                event.preventDefault();
                const notificationId = clickedLink.dataset.notificationId;
                const markAsReadUrl = clickedLink.dataset.markAsReadUrl;
                const targetUrl = clickedLink.getAttribute('href');

                console.log('Fetch: Clicked notification item. ID:', notificationId, 'Mark URL:', markAsReadUrl, 'Target URL:', targetUrl); // BỎ COMMENT LOG

                if (notificationId && markAsReadUrl) {
                    const isPotentiallyUnread = clickedLink.classList.contains('bg-light') || clickedLink.querySelector('.fw-bold');
                    // console.log('Fetch: isPotentiallyUnread:', isPotentiallyUnread); // BỎ COMMENT LOG
                    if (isPotentiallyUnread) {
                        sendMarkRequest(markAsReadUrl, targetUrl, false, clickedLink);
                    } else {
                        console.log('Fetch: Notification item already marked as read (UI). Redirecting.'); // BỎ COMMENT LOG
                        if (targetUrl && targetUrl !== '#') window.location.href = targetUrl;
                    }
                } else if (targetUrl && targetUrl !== '#') {
                    console.log('Fetch: No markAsReadUrl or notificationId. Redirecting.'); // BỎ COMMENT LOG
                    window.location.href = targetUrl;
                }
            }

            const markAllButton = event.target.closest('.mark-all-as-read-btn');
            if (markAllButton) {
                event.preventDefault();
                const markAllUrl = markAllButton.dataset.markAllUrl;
                console.log('Fetch: Clicked mark all as read. URL:', markAllUrl); // BỎ COMMENT LOG
                if (markAllUrl) {
                    sendMarkRequest(markAllUrl, null, true);
                }
            }
        });
    } else {
        console.warn('Fetch: notificationsDropdownContainer not found!'); // BỎ COMMENT LOG
    }

    const messageContainer = document.getElementById('message-container');
    if (messageContainer) {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    const formsToWatch = document.querySelectorAll('#checkin-form, #checkout-form, form[action*="/messages/"]');
    formsToWatch.forEach(form => {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span class="ms-1">Đang xử lý...</span>
                `;
            }
        });
    });

});
// === KẾT THÚC PHẦN THÊM MỚI ===