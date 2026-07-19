<?php

namespace App\Http\Controllers;

use App\Models\TrainerProfile;

class PublicTrainerProfileController extends Controller
{
    public function show(TrainerProfile $trainer)
    {
        $trainer->load('user', 'categories');

        return view('trainers.public-profile', compact('trainer'));
    }
}
