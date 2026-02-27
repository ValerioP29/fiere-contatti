<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function index(Request $request, Exhibition $exhibition)
    {
        $q = trim((string) $request->get('q', ''));

        $contacts = $exhibition->contacts()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(30)
            ->withQueryString();

        return view('contacts.index', compact('exhibition', 'contacts', 'q'));
    }

    public function store(Request $request, Exhibition $exhibition)
    {
        $data = $this->validateContact($request);

        $data['exhibition_id'] = $exhibition->id;
        $data['source'] = 'internal';

        if ($request->hasFile('business_card')) {
            $data['business_card_path'] = $request->file('business_card')
                ->store('business-cards', 'public');
        }

        Contact::create($data);

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto creato.');
    }

    public function update(Request $request, Exhibition $exhibition, Contact $contact)
    {
        abort_if($contact->exhibition_id !== $exhibition->id, 404);

        $data = $this->validateContact($request);

        if ($request->hasFile('business_card')) {
            if ($contact->business_card_path) {
                Storage::disk('public')->delete($contact->business_card_path);
            }
            $data['business_card_path'] = $request->file('business_card')
                ->store('business-cards', 'public');
        }

        $contact->update($data);

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto aggiornato.');
    }

    public function destroy(Exhibition $exhibition, Contact $contact)
    {
        abort_if($contact->exhibition_id !== $exhibition->id, 404);

        if ($contact->business_card_path) {
            Storage::disk('public')->delete($contact->business_card_path);
        }

        $contact->delete();

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto eliminato.');
    }

    public function exportCsv(Request $request, Exhibition $exhibition)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = $exhibition->contacts()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->get([
                'first_name','last_name','email','phone','company','note','source','created_at'
            ]);

        $filename = 'contatti_fiera_'.$exhibition->id.'.csv';

        $out = fopen('php://temp', 'w+');

        // BOM per Excel (UTF-8)
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Nome','Cognome','Email','Telefono','Azienda','Note','Fonte','Creato il']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->first_name,
                $r->last_name,
                $r->email,
                $r->phone,
                $r->company,
                $r->note,
                $r->source,
                optional($r->created_at)->format('Y-m-d H:i'),
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function validateContact(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required','string','max:255'],
            'last_name'  => ['required','string','max:255'],
            'email'      => ['nullable','email','max:255'],
            'phone'      => ['nullable','string','max:50'],
            'company'    => ['nullable','string','max:255'],
            'note'       => ['nullable','string'],
            'business_card' => ['nullable','file','max:5120','mimes:jpg,jpeg,png,pdf,webp'],
        ]);
    }
}