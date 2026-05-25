<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;

class EnterpriseFichaController extends Controller
{
    public function show(Enterprise $enterprise)
    {
        $enterprise->load([
            'enterprise_documents',
            'enterprise_addresses',
            'enterprise_contacts',
            'enterprise_bank_accounts',
            'enterprise_representatives',
            'legalCases.lawyers',
        ]);

        return view('fichas.enterprise', compact('enterprise'));
    }
}
