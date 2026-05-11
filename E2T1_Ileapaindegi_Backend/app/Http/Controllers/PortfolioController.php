<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Portfolio::with(['student', 'service']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $entries = $query->latest()->get()->map(fn ($e) => $this->formatEntry($e));

        return response()->json($entries);
    }

    public function show($id)
    {
        $entry = Portfolio::with(['student', 'service'])->findOrFail($id);
        return response()->json($this->formatEntry($entry));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'service_id' => 'nullable|exists:services,id',
            'category'   => 'required|in:cortes,colores,mechas,tratamientos',
            'photo_before' => 'required|image|max:5120',
            'photo_after'  => 'required|image|max:5120',
            'notes'      => 'nullable|string|max:500',
        ]);

        $beforePath = $request->file('photo_before')->store('portfolio', 'public');
        $afterPath  = $request->file('photo_after')->store('portfolio', 'public');

        $entry = Portfolio::create([
            'student_id'   => $request->student_id,
            'service_id'   => $request->service_id,
            'category'     => $request->category,
            'photo_before' => $beforePath,
            'photo_after'  => $afterPath,
            'notes'        => $request->notes,
        ]);

        return response()->json($this->formatEntry($entry->load(['student', 'service'])), 201);
    }

    public function destroy($id)
    {
        $entry = Portfolio::findOrFail($id);

        Storage::disk('public')->delete([$entry->photo_before, $entry->photo_after]);

        $entry->delete();

        return response()->json(null, 204);
    }

    private function formatEntry(Portfolio $entry): array
    {
        return [
            'id'           => $entry->id,
            'student_id'   => $entry->student_id,
            'student'      => $entry->student ? [
                'id'       => $entry->student->id,
                'name'     => $entry->student->name,
                'surnames' => $entry->student->surnames,
            ] : null,
            'service_id'   => $entry->service_id,
            'service'      => $entry->service ? [
                'id'   => $entry->service->id,
                'name' => $entry->service->name,
            ] : null,
            'category'     => $entry->category,
            'photo_before' => Storage::disk('public')->url($entry->photo_before),
            'photo_after'  => Storage::disk('public')->url($entry->photo_after),
            'notes'        => $entry->notes,
            'created_at'   => $entry->created_at,
        ];
    }
}
