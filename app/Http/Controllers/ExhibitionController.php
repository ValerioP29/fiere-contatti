<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExhibitionController extends Controller
{
    public function index()
    {
        $exhibitions = Exhibition::query()
            ->orderByDesc('date')
            ->paginate(20);

        return view('exhibitions.index', compact('exhibitions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'date' => ['required','date'],
            'company' => ['nullable','string','max:255'],
        ]);

        Exhibition::create($data);

        return redirect()->route('exhibitions.index')->with('status', 'Fiera creata.');
    }

    public function update(Request $request, Exhibition $exhibition)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'date' => ['required','date'],
            'company' => ['nullable','string','max:255'],
        ]);

        $exhibition->update($data);

        return redirect()->route('exhibitions.index')->with('status', 'Fiera aggiornata.');
    }

    public function destroy(Exhibition $exhibition)
    {
        $exhibition->delete();
        return redirect()->route('exhibitions.index')->with('status', 'Fiera eliminata.');
    }

    public function generatePublicLink(Exhibition $exhibition)
    {
        if (!$exhibition->public_token) {
            $exhibition->public_token = Str::random(48);
            $exhibition->save();
        }

        return response()->json([
            'url' => route('public.form', ['token' => $exhibition->public_token]),
        ]);
    }
}