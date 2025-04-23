<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternRequest;
use App\Models\Intern;
use Illuminate\Http\Request;

class InternController extends Controller
{
    public function index()
    {
        return Intern::with('user')->get();
    }

    public function store(StoreInternRequest $request)
    {
        $intern = Intern::create($request->validated());
        return response()->json($intern, 201);
    }

    public function show(Intern $intern)
    {
        return $intern->load('user');
    }

    public function update(StoreInternRequest $request, Intern $intern)
    {
        $intern->update($request->validated());
        return $intern;
    }

    public function destroy(Intern $intern)
    {
        $intern->delete();
        return response()->json(null, 204);
    }
}