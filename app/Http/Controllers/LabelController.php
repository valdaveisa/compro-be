<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        return response()->json(Label::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $label = Label::create($data);

        return response()->json($label, 201);
    }

    public function update(Request $request, Label $label)
    {
        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $label->update($data);

        return response()->json($label);
    }

    public function destroy(Label $label)
    {
        $label->delete();

        return response()->json(['message' => 'Label deleted']);
    }
}
