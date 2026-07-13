<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrainerCategoryController extends Controller
{
    public function index()
    {
        $categories = TrainerCategory::withCount('trainerProfiles')->orderBy('name')->get();

        return view('admin.trainer-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.trainer-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_assessment_category'] = $request->boolean('is_assessment_category');
        $validated['is_active'] = $request->boolean('is_active', true);

        TrainerCategory::create($validated);

        return redirect()->route('admin.trainer-categories.index')->with('status', 'Category created.');
    }

    public function edit(TrainerCategory $trainerCategory)
    {
        return view('admin.trainer-categories.edit', ['category' => $trainerCategory]);
    }

    public function update(Request $request, TrainerCategory $trainerCategory)
    {
        $validated = $this->validateCategory($request, $trainerCategory);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_assessment_category'] = $request->boolean('is_assessment_category');
        $validated['is_active'] = $request->boolean('is_active', true);

        $trainerCategory->update($validated);

        return redirect()->route('admin.trainer-categories.index')->with('status', 'Category updated.');
    }

    public function destroy(TrainerCategory $trainerCategory)
    {
        if ($trainerCategory->trainerProfiles()->exists()) {
            return back()->with('error', 'Cannot delete a category with trainers assigned to it. Deactivate it instead.');
        }

        $trainerCategory->delete();

        return redirect()->route('admin.trainer-categories.index')->with('status', 'Category deleted.');
    }

    private function validateCategory(Request $request, ?TrainerCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('trainer_categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string'],
        ]);
    }
}
