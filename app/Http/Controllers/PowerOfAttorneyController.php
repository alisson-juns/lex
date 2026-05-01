<?php

namespace App\Http\Controllers;

use App\Models\PowerOfAttorney;
use App\Services\PowerOfAttorneyService;

class PowerOfAttorneyController extends Controller
{
    public function __construct(private PowerOfAttorneyService $service) {}

    public function pdf(PowerOfAttorney $powerOfAttorney)
    {
        $pdf = $this->service->generate($powerOfAttorney);

        $filename = 'procuracao-' . $powerOfAttorney->id . '.pdf';

        return $pdf->stream($filename);
    }
}