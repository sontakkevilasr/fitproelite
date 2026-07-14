<?php

use App\Http\Controllers\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\InterestLevelController;
use App\Http\Controllers\Admin\NotificationLogController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TrainerCategoryController;
use App\Http\Controllers\Admin\TrainerController;
use App\Http\Controllers\Admin\TrialController as AdminTrialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Counsellor\ClientCallController;
use App\Http\Controllers\Counsellor\ClientController as CounsellorClientController;
use App\Http\Controllers\Counsellor\FollowUpController;
use App\Http\Controllers\Counsellor\LookupController;
use App\Http\Controllers\Counsellor\TrialController as CounsellorTrialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTrainerProfileController;
use App\Http\Controllers\Trainer\AssessmentController;
use App\Http\Controllers\Trainer\AvailabilityController;
use App\Http\Controllers\Trainer\BlockedSlotController;
use App\Http\Controllers\Trainer\CalendarController as TrainerCalendarController;
use App\Http\Controllers\Trainer\SessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// Serves the public storage disk (trainer photos) directly through PHP instead
// of relying on the public/storage symlink. Some shared hosts (this app has
// been deployed to a LiteSpeed/CageFS host where this is the case) restrict
// or silently break symlink-following in ways `php artisan storage:link`
// and .htaccess FollowSymLinks can't work around. This route is a drop-in
// replacement — it matches the exact URL shape `asset('storage/...')`
// already generates, so no other code needed to change.
Route::get('/storage/{path}', function (string $path) {
    try {
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    } catch (\League\Flysystem\PathTraversalDetected $e) {
        abort(404);
    }
})->where('path', '.*')->name('storage.public.serve');

Route::get('/trainers/{trainer}/profile', [PublicTrainerProfileController::class, 'show'])->name('trainers.public-profile');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::resource('trainer-categories', TrainerCategoryController::class)->except(['show']);
    Route::resource('packages', PackageController::class)->except(['show']);
    Route::resource('interest-levels', InterestLevelController::class)->except(['show']);

    Route::get('trainers', [TrainerController::class, 'index'])->name('trainers.index');
    Route::get('trainers/{trainer}', [TrainerController::class, 'show'])->name('trainers.show');

    Route::get('calendar', [AdminCalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [AdminCalendarController::class, 'events'])->name('calendar.events');

    Route::get('clients', [AdminClientController::class, 'index'])->name('clients.index');

    Route::get('trials', [AdminTrialController::class, 'index'])->name('trials.index');
    Route::get('trials/{trial}', [AdminTrialController::class, 'show'])->name('trials.show');

    Route::get('notifications', [NotificationLogController::class, 'index'])->name('notifications.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});

Route::middleware(['auth', 'role:trainer'])->prefix('trainer')->name('trainer.')->group(function () {
    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::put('availability', [AvailabilityController::class, 'update'])->name('availability.update');

    Route::get('blocked-slots', [BlockedSlotController::class, 'index'])->name('blocked-slots.index');
    Route::post('blocked-slots', [BlockedSlotController::class, 'store'])->name('blocked-slots.store');
    Route::delete('blocked-slots/{blockedSlot}', [BlockedSlotController::class, 'destroy'])->name('blocked-slots.destroy');

    Route::get('calendar', [TrainerCalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [TrainerCalendarController::class, 'events'])->name('calendar.events');

    Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::patch('sessions/{trialSession}/status', [SessionController::class, 'updateStatus'])->name('sessions.status');

    Route::get('assessments/{trial}/create', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('assessments/{trial}', [AssessmentController::class, 'store'])->name('assessments.store');
});

Route::middleware(['auth', 'role:counsellor'])->prefix('counsellor')->name('counsellor.')->group(function () {
    Route::get('lookup', [LookupController::class, 'index'])->name('lookup.index');
    Route::get('lookup/search', [LookupController::class, 'search'])->middleware('throttle:30,1')->name('lookup.search');

    Route::get('clients', [CounsellorClientController::class, 'index'])->name('clients.index');
    Route::post('clients', [CounsellorClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [CounsellorClientController::class, 'show'])->name('clients.show');
    Route::post('clients/{client}/calls', [ClientCallController::class, 'store'])->name('clients.calls.store');

    Route::get('follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');

    Route::get('trials', [CounsellorTrialController::class, 'index'])->name('trials.index');
    Route::patch('trials/{trial}/outcome', [CounsellorTrialController::class, 'updateOutcome'])->name('trials.outcome');
});

Route::middleware(['auth'])
    ->prefix('booking/{type}/clients/{client}')
    ->where(['type' => 'pre-visit|free-trial'])
    ->name('booking.')
    ->group(function () {
        Route::get('category', [BookingController::class, 'selectCategory'])->name('category');
        Route::get('trainers', [BookingController::class, 'selectTrainer'])->name('trainers');
        Route::get('trainers/{trainerProfile}/calendar', [BookingController::class, 'calendar'])->name('calendar');
        Route::get('trainers/{trainerProfile}/slots', [BookingController::class, 'slots'])->name('slots');
        Route::post('trainers/{trainerProfile}/suggest', [BookingController::class, 'suggestSessions'])->name('suggest');
        Route::post('trainers/{trainerProfile}', [BookingController::class, 'store'])->name('store');
    });

require __DIR__.'/auth.php';
