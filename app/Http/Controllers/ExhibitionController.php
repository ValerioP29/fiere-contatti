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
            ->orderByDesc('start_date')
            ->orderByDesc('date')
            ->paginate(20);

        return view('exhibitions.index', compact('exhibitions'));
    }

    public function store(StoreExhibitionRequest $request): RedirectResponse
    {
        Exhibition::create([
            ...$this->normalizeDatePayload($request->validated()),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('exhibitions.index')->with('status', 'Fiera creata.');
    }

    public function update(StoreExhibitionRequest $request, Exhibition $exhibition): RedirectResponse
    {
        $this->ensureOwnership($exhibition);

        $exhibition->update($this->normalizeDatePayload($request->validated()));

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

    private function normalizeDatePayload(array $data): array
    {
        if (! empty($data['date'])) {
            $data['start_date'] = null;
            $data['end_date'] = null;

            return $data;
        }

        $data['date'] = $data['start_date'] ?? null;

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
