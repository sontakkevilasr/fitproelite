<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterestLevel;
use Illuminate\Http\Request;

class InterestLevelController extends Controller
{
    public function index()
    {
        $interestLevels = InterestLevel::orderBy('sort_order')->get();

        return view('admin.interest-levels.index', compact('interestLevels'));
    }

    public function create()
    {
        return view('admin.interest-levels.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateLevel($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        InterestLevel::create($validated);

        return redirect()->route('admin.interest-levels.index')->with('status', 'Interest level created.');
    }

    public function edit(InterestLevel $interestLevel)
    {
        return view('admin.interest-levels.edit', compact('interestLevel'));
    }

    public function update(Request $request, InterestLevel $interestLevel)
    {
        $validated = $this->validateLevel($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        $interestLevel->update($validated);

        return redirect()->route('admin.interest-levels.index')->with('status', 'Interest level updated.');
    }

    public function destroy(InterestLevel $interestLevel)
    {
        $interestLevel->delete();

        return redirect()->route('admin.interest-levels.index')->with('status', 'Interest level deleted.');
    }

    private function validateLevel(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
