<?php

namespace App\Services;

use App\Models\PowerOfAttorney;
use App\Models\FirmSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class PowerOfAttorneyService
{
    public function render(PowerOfAttorney $poa): string
{
    $poa->load([
        'client.client_addresses',
        'client.client_documents',
        'client.client_contacts',
        'template',
        'lawyers',
    ]);

    $client   = $poa->client;
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

    // Monta parágrafo dos advogados a partir da pivot
    $firmLawyers = $poa->lawyers->isNotEmpty()
        ? $poa->lawyers->map(fn ($l) =>
            $l->name . ($l->oab ? ' OAB ' . $l->oab . '/' . $l->oab_state : '')
          )->join(' e ')
        : ($settings->firm_lawyers ?? '');

    $values = [
        'client_name'           => $client->name ?? '',
        'client_nationality'    => $client->nationality ?? '',
        'client_marital_status' => $client->marital_status ?? '',
        'client_profession'     => $client->profession ?? '',
        'client_rg'             => $client->client_documents?->rg ?? '',
        'client_cpf'            => $client->client_documents?->cpf ?? '',
        'client_mother'         => $client->mother ?? '',
        'client_father'         => $client->father ?? '',
        'client_date_of_birth'  => $client->date_of_birth
            ? \Carbon\Carbon::parse($client->date_of_birth)->format('d/m/Y')
            : '',
        'client_address'        => $address,
        'client_email'          => $client->client_contacts?->email ?? '',
        'firm_lawyers'          => $firmLawyers,
        'specific_text'         => $poa->specific_text,
    ];

    $body = html_entity_decode($poa->template->body_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return preg_replace_callback(
        '/<span[^>]*data-placeholder="([^"]+)"[^>]*>.*?<\/span>/s',
        fn (array $matches) => $values[$matches[1]] ?? $matches[0],
        $body
    );
}
    public function generate(PowerOfAttorney $poa): string
    {
        $poa->load('client');
        $html = $poa->rendered_body ?? $this->render($poa);
        $firm = FirmSetting::instance();
    
        $logoBase64 = null;
        if ($firm->firm_logo && \Storage::disk('public')->exists($firm->firm_logo)) {
            $mime       = mime_content_type(\Storage::disk('public')->path($firm->firm_logo));
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(\Storage::disk('public')->get($firm->firm_logo));
        }
    
        $pdf = Pdf::loadView('pdf.power-of-attorney', [
            'body'        => $html,
            'firm'        => $firm,
            'firmCity'    => $firm->firm_city ?? '',
            'currentDate' => now()->translatedFormat('d \d\e F \d\e Y'),
            'client'      => $poa->client,
            'logoBase64'  => $logoBase64,
        ])->setPaper('a4');
    
        $path = 'procuracoes/procuracao-' . $poa->id . '.pdf';
    
        \Storage::disk('public')->put($path, $pdf->output());
    
        $poa->update(['pdf_path' => $path]);
    
        return $path;
    }
}