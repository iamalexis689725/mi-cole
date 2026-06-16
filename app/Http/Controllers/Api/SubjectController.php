<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return Subject::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $subject = Subject::create([
            'name' => $request->name
        ]);

        return response()->json($subject);
    }

    public function profesores(int $id)
    {
        $profesores = Profesor::with('user')
            ->whereHas('subjects', function ($q) use ($id) {
                $q->where('subjects.id', $id);
            })
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();

        return response()->json([
            'data' => $profesores
        ]);
    }
}
