<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientFichaController extends Controller
{
    public function show(Client $client)
    {
        $client->load([
            'client_documents',
            'client_addresses',
            'client_contacts',
            'client_bank_accounts',
            'spouse',
            'wards',
            'legalCases.lawyers',
        ]);

        return view('fichas.client', compact('client'));
    }
}
