<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class PublicContactController extends Controller
{
    public function show(string $token)
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();
        return view('public.form', compact('exhibition', 'token'));
    }

    public function store(Request $request, string $token)
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();

        $data = $request->validate([
            'first_name' => ['required','string','max:255'],
            'last_name'  => ['required','string','max:255'],
            'email'      => ['nullable','email','max:255'],
            'phone'      => ['nullable','string','max:50'],
            'company'    => ['nullable','string','max:255'],
            'note'       => ['nullable','string'],
            'business_card' => ['nullable','file','max:5120','mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $data['exhibition_id'] = $exhibition->id;
        $data['source'] = 'public';

        if ($request->hasFile('business_card')) {
            $data['business_card_path'] = $request->file('business_card')
                ->store('business-cards', 'public');
        }

        Contact::create($data);

        return redirect()->route('public.thanks', ['token' => $token]);
    }

    public function thanks(string $token)
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();
        return view('public.thanks', compact('exhibition'));
    }
}