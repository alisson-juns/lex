<?php

namespace App\Services;

use App\Models\GratuityDeclaration;
use App\Models\FirmSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GratuityDeclarationService
{
    public function render(GratuityDeclaration $declaration): string
    {
        $declaration->load([
            'client.client_addresses',
            'client.client_documents',
            'client.client_contacts',
            'template',
        ]);

        $client   = $declaration->client;
        $settings = FirmSetting::instance();

        $address = collect([
            $client->client_addresses?->street,
            $client->client_addresses?->number,
            $client->client_addresses?->complement,
            $client->client_addresses?->district,
            $client->client_addresses?->city,
            $client->client_addresses?->state,
            $client->client_addresses?->zipcode,
        ])->filter()->join(', ');

        $values = [
            'client_name'           => strtoupper($client->name ?? ''),
            'client_nationality'    => $client->nationality ?? '',
            'client_marital_status' => $client->marital_status ?? '',
            'client_profession'     => $client->profession ?? '',
            'client_rg'             => $client->client_documents?->rg ?? '',
            'client_cpf'            => $client->client_documents?->cpf ?? '',
            'client_mother'         => $client->mother ?? '',
            'client_father'         => $client->father ?? '',
            'client_date_of_birth'  => $client->date_of_birth
                ? Carbon::parse($client->date_of_birth)->format('d/m/Y')
                : '',
            'client_address'        => $address,
            'client_email'          => $client->client_contacts?->email ?? '',
            'city_date'             => ($settings->firm_city ?? '') . ', ' . now()->translatedFormat('d \d\e F \d\e Y'),
        ];

        $body = html_entity_decode($declaration->template->body_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $body = preg_replace_callback(
            '/<span[^>]*data-placeholder="([^"]+)"[^>]*>.*?<\/span>/s',
            fn (array $matches) => $values[$matches[1]] ?? $matches[0],
            $body
        );

        foreach ($values as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }

    public function generate(GratuityDeclaration $declaration): string
    {
        $html = $declaration->rendered_body ?? $this->render($declaration);
        $firm = FirmSetting::instance();


        $pdf = Pdf::loadView('pdf.gratuity-declaration', [
            'body'       => $html,
            'firm'       => $firm,

        ])->setPaper('a4');

        $path = 'gratuity-declarations/declaration-' . $declaration->id . '.pdf';

        Storage::disk('public')->makeDirectory('gratuity-declarations');
        Storage::disk('public')->put($path, $pdf->output());

        $declaration->update(['pdf_path' => $path]);

        return $path;
    }
}
