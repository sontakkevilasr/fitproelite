<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\TrainerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorContract;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('trainerCategories')->orderBy('name')->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $categories = TrainerCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.packages.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);

        $rows = collect($validated['rows']);

        $package = Package::create([
            'name' => $validated['name'],
            'price' => $validated['price'] ?? null,
            'description' => $validated['description'] ?? null,
            'week_days' => $validated['week_days'],
            'sessions_count' => $rows->sum('sessions'),
            'trial_sessions_count' => $validated['trial_sessions_count'] ?? 3,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $package->trainerCategories()->sync($this->pivotData($rows));

        return redirect()->route('admin.packages.index')->with('status', 'Package created.');
    }

    public function edit(Package $package)
    {
        $categories = TrainerCategory::where('is_active', true)->orderBy('name')->get();
        $package->load('trainerCategories');

        return view('admin.packages.edit', compact('package', 'categories'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $this->validatePackage($request);

        $rows = collect($validated['rows']);

        $package->update([
            'name' => $validated['name'],
            'price' => $validated['price'] ?? null,
            'description' => $validated['description'] ?? null,
            'week_days' => $validated['week_days'],
            'sessions_count' => $rows->sum('sessions'),
            'trial_sessions_count' => $validated['trial_sessions_count'] ?? 3,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $package->trainerCategories()->sync($this->pivotData($rows));

        return redirect()->route('admin.packages.index')->with('status', 'Package updated.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'Package deleted.');
    }

    private function pivotData(\Illuminate\Support\Collection $rows): array
    {
        return $rows
            ->mapWithKeys(fn ($row) => [(int) $row['trainer_category_id'] => ['sessions' => (int) $row['sessions']]])
            ->all();
    }

    private function validatePackage(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'week_days' => ['required', Rule::in(Package::WEEK_DAYS_OPTIONS)],
            'trial_sessions_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.trainer_category_id' => ['required', 'distinct', 'exists:trainer_categories,id'],
            'rows.*.sessions' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'rows.required' => 'Add at least one trainer type with a session count.',
            'rows.*.trainer_category_id.distinct' => 'Each trainer type can only be added once — combine the sessions into a single row instead.',
        ]);

        $validator->after(function (ValidatorContract $validator) use ($request) {
            $weekDays = (int) $request->input('week_days');
            $rows = collect($request->input('rows', []));
            $total = $rows->sum(fn ($row) => (int) ($row['sessions'] ?? 0));
            $max = $weekDays * Package::WEEKS_PER_PACKAGE;

            if ($weekDays && $total > $max) {
                $validator->errors()->add(
                    'rows',
                    "Total sessions ({$total}) exceed the max allowed for {$weekDays} days/week ({$max} sessions over ".Package::WEEKS_PER_PACKAGE.' weeks).'
                );
            }
        });

        return $validator->validate();
    }
}
