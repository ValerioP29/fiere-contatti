<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExhibitionRequest;
use App\Models\Exhibition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExhibitionController extends Controller
{
    public function index(): View
    {
        $exhibitions = Exhibition::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('exhibitions.index', compact('exhibitions'));
    }

    public function create(): View
    {
        return view('exhibitions.create');
    }

    public function show(Request $request, Exhibition $exhibition): View
    {
        $this->ensureOwnership($exhibition);

        if (! $exhibition->public_token) {
            $exhibition->update(['public_token' => (string) Str::ulid()]);
            $exhibition->refresh();
        }

        $q = trim((string) $request->string('q'));

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
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $publicUrl = route('public.form', ['token' => $exhibition->public_token]);

        return view('exhibitions.show', compact('exhibition', 'contacts', 'q', 'publicUrl'));
    }

    public function edit(Exhibition $exhibition): View
    {
        $this->ensureOwnership($exhibition);

        return view('exhibitions.edit', compact('exhibition'));
    }

    public function store(StoreExhibitionRequest $request): RedirectResponse
    {
        Exhibition::create([
            ...$this->normalizePayload($request->validated()),
            'user_id' => auth()->id(),
            'public_token' => (string) Str::ulid(),
        ]);

        return redirect()->route('exhibitions.index')->with('status', 'Fiera creata.');
    }

    public function update(StoreExhibitionRequest $request, Exhibition $exhibition): RedirectResponse
    {
        $this->ensureOwnership($exhibition);

        $exhibition->update($this->normalizePayload($request->validated()));

        return redirect()->route('exhibitions.index')->with('status', 'Fiera aggiornata.');
    }

    public function destroy(Exhibition $exhibition): RedirectResponse
    {
        $this->ensureOwnership($exhibition);
        $exhibition->delete();

        return redirect()->route('exhibitions.index')->with('status', 'Fiera eliminata.');
    }

    public function generatePublicLink(Request $request, Exhibition $exhibition): JsonResponse
    {
        $this->ensureOwnership($exhibition);

        $regenerate = $request->boolean('regenerate');
        if (! $exhibition->public_token || $regenerate) {
            $exhibition->update(['public_token' => (string) Str::ulid()]);
        }

        return response()->json([
            'url' => route('public.form', ['token' => $exhibition->public_token]),
        ]);
    }

    private function normalizePayload(array $data): array
    {
        $data['start_date'] = null;
        $data['end_date'] = null;

        return $data;
    }

    private function ensureOwnership(Exhibition $exhibition): void
    {
        if ($exhibition->user_id !== auth()->id()) {
            Log::warning('Tentativo di accesso a fiera non autorizzata', [
                'user_id' => auth()->id(),
                'exhibition_id' => $exhibition->id,
            ]);
            abort(404);
        }
    }
}
