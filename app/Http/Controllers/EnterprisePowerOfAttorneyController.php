<?php

namespace App\Http\Controllers;

use App\Models\EnterprisePowerOfAttorney;
use App\Services\EnterprisePowerOfAttorneyService;

class EnterprisePowerOfAttorneyController extends Controller
{
    public function __construct(private EnterprisePowerOfAttorneyService $service)
    {
    }

    public function pdf(EnterprisePowerOfAttorney $enterprisePowerOfAttorney)
    {
        $path = $this->service->generate($enterprisePowerOfAttorney);

        return response()->file(
            \Storage::disk('public')->path($path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
