<?php

namespace App\Http\Controllers;

use App\Models\GratuityDeclaration;
use App\Services\GratuityDeclarationService;

class GratuityDeclarationController extends Controller
{
    public function __construct(private GratuityDeclarationService $service)
    {
    }

    public function pdf(GratuityDeclaration $gratuityDeclaration)
    {
        $path = $this->service->generate($gratuityDeclaration);

        return response()->file(
            \Storage::disk('public')->path($path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
