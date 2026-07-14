<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles', 'trainerProfile.category')
            ->when($request->filled('role'), fn ($q) => $q->role($request->string('role')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $categories = TrainerCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $user->assignRole($validated['role']);

            if ($validated['role'] === 'trainer') {
                $photoPath = $request->hasFile('photo')
                    ? $request->file('photo')->store('trainer-photos', 'public')
                    : null;

                TrainerProfile::create([
                    'user_id' => $user->id,
                    'trainer_category_id' => $validated['trainer_category_id'],
                    'photo_path' => $photoPath,
                    'bio' => $validated['bio'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'session_duration_minutes' => $validated['session_duration_minutes'] ?? 60,
                    'is_active' => true,
                ]);
            }

            return $user;
        });

        return redirect()->route('admin.users.index')->with('status', "{$user->name} was created.");
    }

    public function edit(User $user)
    {
        $user->load('roles', 'trainerProfile');
        $categories = TrainerCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'categories'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user);

        DB::transaction(function () use ($validated, $request, $user) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                ...(filled($validated['password'] ?? null) ? ['password' => Hash::make($validated['password'])] : []),
            ]);

            $user->syncRoles([$validated['role']]);

            if ($validated['role'] === 'trainer') {
                $profile = $user->trainerProfile ?? new TrainerProfile(['user_id' => $user->id]);

                if ($request->hasFile('photo')) {
                    if ($profile->photo_path) {
                        Storage::disk('public')->delete($profile->photo_path);
                    }
                    $profile->photo_path = $request->file('photo')->store('trainer-photos', 'public');
                }

                $profile->trainer_category_id = $validated['trainer_category_id'];
                $profile->bio = $validated['bio'] ?? null;
                $profile->phone = $validated['phone'] ?? null;
                $profile->session_duration_minutes = $validated['session_duration_minutes'] ?? 60;
                $profile->save();
            }
        });

        return redirect()->route('admin.users.index')->with('status', "{$user->name} was updated.");
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', "{$user->name} is now ".($user->is_active ? 'active' : 'inactive').'.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'counsellor', 'trainer'])],
            'trainer_category_id' => ['required_if:role,trainer', 'nullable', 'exists:trainer_categories,id'],
            'bio' => ['nullable', 'string'],
            'session_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:180'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
