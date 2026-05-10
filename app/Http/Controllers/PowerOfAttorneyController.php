<?php

namespace App\Http\Controllers;

use App\Models\PowerOfAttorney;
use App\Services\PowerOfAttorneyService;

class PowerOfAttorneyController extends Controller
{
    public function __construct(private PowerOfAttorneyService $service) {}

    public function pdf(PowerOfAttorney $powerOfAttorney)
    {
        $path = $this->service->generate($powerOfAttorney);
    
        return response()->file(
            \Storage::disk('public')->path($path),
            ['Content-Type' => 'application/pdf']
        );
    }


}