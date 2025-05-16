<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $reviews = $user->reviewsReceived()->with('reviewer')->latest()->paginate(10);

        return view('student.reviews.index', compact('reviews'));
    }
}