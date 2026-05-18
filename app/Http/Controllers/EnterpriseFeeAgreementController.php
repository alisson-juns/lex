<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseFeeAgreement;
use App\Services\EnterpriseFeeAgreementService;

class EnterpriseFeeAgreementController extends Controller
{
    public function __construct(private EnterpriseFeeAgreementService $service)
    {
    }

    public function pdf(EnterpriseFeeAgreement $enterpriseFeeAgreement)
    {
        $path = $this->service->generate($enterpriseFeeAgreement);

        return response()->file(
            \Storage::disk('public')->path($path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
