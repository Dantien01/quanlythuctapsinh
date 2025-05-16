<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs; // Hoặc không cần nếu bạn không dùng Job dispatching trực tiếp từ controller
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Quan trọng: Kế thừa từ BaseController của Laravel

abstract class Controller extends BaseController // <<< SỬA Ở ĐÂY
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests; // <<< SỬA Ở ĐÂY (Thêm DispatchesJobs nếu cần)
    // Nếu bạn dùng Laravel phiên bản cũ hơn (ví dụ < 9), có thể chỉ là:
    // use AuthorizesRequests, ValidatesRequests;
}