<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Exhibition;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicContactController extends Controller
{
    public function show(string $token): View
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();

        return view('public.form', compact('exhibition', 'token'));
    }

    public function store(StoreContactRequest $request, string $token): RedirectResponse
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();

        $data = $request->validated();
        unset($data['contact_file']);

        $data['exhibition_id'] = $exhibition->id;
        $data['source'] = 'public';

        if ($request->hasFile('contact_file')) {
            $file = $request->file('contact_file');
            $data = [
                ...$data,
                'file_path' => $file->store('contact-files'),
                'file_original_name' => $file->getClientOriginalName(),
                'file_mime' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ];
        }

        Contact::create($data);

        return redirect()->route('public.thanks', ['token' => $token]);
    }

    public function thanks(string $token): View
    {
        $exhibition = Exhibition::where('public_token', $token)->firstOrFail();

        return view('public.thanks', compact('exhibition'));
    }
}
