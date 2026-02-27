<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Exhibition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function index(Request $request, Exhibition $exhibition)
    {
        $this->ensureOwnership($exhibition);

        $q = trim((string) $request->get('q', ''));

        $contacts = $exhibition->contacts()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('note', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(30)
            ->withQueryString();

        return view('contacts.index', compact('exhibition', 'contacts', 'q'));
    }

    public function store(StoreContactRequest $request, Exhibition $exhibition): RedirectResponse
    {
        $this->ensureOwnership($exhibition);

        $data = $request->validated();
        unset($data['contact_file']);

        $data['exhibition_id'] = $exhibition->id;
        $data['source'] = 'internal';
        $data = [...$data, ...$this->extractFileData($request)];

        Contact::create($data);

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto creato.');
    }

    public function update(StoreContactRequest $request, Exhibition $exhibition, Contact $contact): RedirectResponse
    {
        $this->ensureOwnership($exhibition);
        abort_if($contact->exhibition_id !== $exhibition->id, 404);

        $data = $request->validated();
        unset($data['contact_file']);

        if ($request->hasFile('contact_file')) {
            $this->deleteStoredFile($contact);
            $data = [...$data, ...$this->extractFileData($request)];
        }

        $contact->update($data);

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto aggiornato.');
    }

    public function destroy(Exhibition $exhibition, Contact $contact): RedirectResponse
    {
        $this->ensureOwnership($exhibition);
        abort_if($contact->exhibition_id !== $exhibition->id, 404);

        $this->deleteStoredFile($contact);
        $contact->delete();

        return redirect()->route('contacts.index', $exhibition)->with('status', 'Contatto eliminato.');
    }

    public function downloadFile(Exhibition $exhibition, Contact $contact): BinaryFileResponse
    {
        $this->ensureOwnership($exhibition);
        $this->guardContactFileAccess($exhibition, $contact);

        return Storage::download($contact->file_path, $contact->file_original_name ?? basename($contact->file_path));
    }

    public function previewFile(Exhibition $exhibition, Contact $contact): Response|BinaryFileResponse
    {
        $this->ensureOwnership($exhibition);
        $this->guardContactFileAccess($exhibition, $contact);

        $mime = $contact->file_mime ?? Storage::mimeType($contact->file_path);
        if (! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) {
            return redirect()->route('contacts.file.download', [$exhibition, $contact]);
        }

        return response(Storage::get($contact->file_path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.($contact->file_original_name ?? basename($contact->file_path)).'"',
        ]);
    }

    public function exportExcel(Request $request, Exhibition $exhibition): Response
    {
        $this->ensureOwnership($exhibition);

        $q = trim((string) $request->get('q', ''));
        $rows = $exhibition->contacts()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('note', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->get(['first_name', 'last_name', 'email', 'phone', 'company', 'note', 'source', 'created_at']);

        $filename = 'contatti_fiera_'.$exhibition->id.'.xls';
        $headers = ['Nome', 'Cognome', 'Email', 'Telefono', 'Azienda', 'Note', 'Fonte', 'Creato il'];

        $xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Worksheet ss:Name="Contatti"><Table><Row>';

        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">'.e($header).'</Data></Cell>';
        }

        $xml .= '</Row>';

        foreach ($rows as $r) {
            $xml .= '<Row>';
            foreach ([$r->first_name, $r->last_name, $r->email, $r->phone, $r->company, $r->note, $r->source, optional($r->created_at)->format('Y-m-d H:i')] as $value) {
                $xml .= '<Cell><Data ss:Type="String">'.e((string) ($value ?? '')).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function extractFileData(StoreContactRequest $request): array
    {
        if (! $request->hasFile('contact_file')) {
            return [];
        }

        $file = $request->file('contact_file');

        return [
            'file_path' => $file->store('contact-files'),
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    private function deleteStoredFile(Contact $contact): void
    {
        if ($contact->file_path) {
            Storage::delete($contact->file_path);
        }
    }

    private function guardContactFileAccess(Exhibition $exhibition, Contact $contact): void
    {
        abort_if($contact->exhibition_id !== $exhibition->id, 404);
        abort_unless($contact->file_path, 404);
        abort_unless(Storage::exists($contact->file_path), 404);
    }

    private function ensureOwnership(Exhibition $exhibition): void
    {
        if ($exhibition->user_id !== auth()->id()) {
            Log::warning('Tentativo di accesso a contatti non autorizzato', [
                'user_id' => auth()->id(),
                'exhibition_id' => $exhibition->id,
            ]);
            abort(404);
        }
    }
}
