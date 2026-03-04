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
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request, Exhibition $exhibition): RedirectResponse
    {
        $this->ensureOwnership($exhibition);

        $data = $request->validated();
        unset($data['contact_file']);

        $data['exhibition_id'] = $exhibition->id;
        $data['source'] = 'internal';
        $data = [...$data, ...$this->extractFileData($request)];

        Contact::create($data);

        return redirect()->route('exhibitions.show', $exhibition)->with('status', 'Contatto aggiunto.');
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

        return redirect()->route('exhibitions.show', $exhibition)->with('status', 'Contatto aggiornato.');
    }

    public function destroy(Exhibition $exhibition, Contact $contact): RedirectResponse
    {
        $this->ensureOwnership($exhibition);
        abort_if($contact->exhibition_id !== $exhibition->id, 404);

        $this->deleteStoredFile($contact);
        $contact->delete();

        return redirect()->route('exhibitions.show', $exhibition)->with('status', 'Contatto eliminato.');
    }

    public function downloadFile(Exhibition $exhibition, Contact $contact): StreamedResponse
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
            return redirect()->route('exhibitions.contacts.download', [$exhibition, $contact]);
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
            ->when($q !== '', fn ($query) => $query->search($q))
            ->orderBy('last_name')
            ->get(['first_name', 'last_name', 'email', 'phone', 'company', 'note', 'source', 'created_at']);

        $safeRows = $rows->map(fn (Contact $contact) => [
            'Fiera' => $this->sanitizeSpreadsheetText($exhibition->name),
            'Data fiera' => $this->sanitizeSpreadsheetText($exhibition->display_date),
            'Nome' => $this->sanitizeSpreadsheetText($contact->first_name),
            'Cognome' => $this->sanitizeSpreadsheetText($contact->last_name),
            'Email' => $this->sanitizeSpreadsheetText($contact->email),
            'Telefono' => $this->sanitizeSpreadsheetText($contact->phone),
            'Azienda' => $this->sanitizeSpreadsheetText($contact->company),
            'Note' => $this->sanitizeSpreadsheetText($contact->note),
            'Fonte' => $this->sanitizeSpreadsheetText($contact->source),
            'Creato il' => optional($contact->created_at)->format('Y-m-d H:i') ?? '',
        ]);

        $headers = ['Fiera', 'Data fiera', 'Nome', 'Cognome', 'Email', 'Telefono', 'Azienda', 'Note', 'Fonte', 'Creato il'];
        $columnWidths = $this->calculateColumnWidths($headers, $safeRows->all());

        $tmpPath = tempnam(sys_get_temp_dir(), 'contacts-export-');
        $xlsxPath = $tmpPath.'.xlsx';
        @unlink($tmpPath);

        $writer = new Writer();
        if (method_exists($writer, 'setColumnWidth')) {
            foreach ($columnWidths as $columnIndex => $columnWidth) {
                $writer->setColumnWidth($columnWidth, $columnIndex + 1);
            }
        }

        $writer->openToFile($xlsxPath);
        $writer->addRow(Row::fromValues($headers));

        foreach ($safeRows as $row) {
            $writer->addRow(Row::fromValues(array_values($row)));
        }

        $writer->close();

        return response()->download(
            $xlsxPath,
            'contatti_fiera_'.$exhibition->id.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function calculateColumnWidths(array $headers, array $rows): array
    {
        $widths = array_map(fn (string $header) => min(mb_strlen($header) + 2, 60), $headers);

        foreach ($rows as $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $length = mb_strlen((string) $value);
                $widths[$columnIndex] = min(max($widths[$columnIndex], $length + 2), 60);
            }
        }

        return $widths;
    }

    private function sanitizeSpreadsheetText(?string $value): string
    {
        $cleanValue = $value ?? '';

        if ($cleanValue !== '' && in_array($cleanValue[0], ['=', '+', '-', '@'], true)) {
            return "'{$cleanValue}";
        }

        return $cleanValue;
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
