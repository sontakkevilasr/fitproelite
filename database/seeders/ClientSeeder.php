<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientCall;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        if (Client::count() > 0) {
            return;
        }

        Client::factory(10)->create()->each(function (Client $client) {
            ClientCall::create([
                'client_id' => $client->id,
                'counsellor_id' => $client->created_by,
                'call_date' => $client->created_at,
                'notes' => 'Initial enquiry call. Interested in '.($client->package?->name ?? 'a package').'.',
                'outcome' => ClientCall::OUTCOME_INTERESTED,
            ]);
        });
    }
}
