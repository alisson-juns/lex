<?php

namespace App\Http\Controllers;

use App\Models\FeeAgreement;
use App\Services\FeeAgreementService;

class FeeAgreementController extends Controller
{
    public function __construct(private FeeAgreementService $service)
    {
    }

    public function pdf(FeeAgreement $feeAgreement)
    {
        $path = $this->service->generate($feeAgreement);

        return response()->file(
            \Storage::disk('public')->path($path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
