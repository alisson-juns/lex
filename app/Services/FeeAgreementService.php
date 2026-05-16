<?php

namespace App\Services;

use App\Models\FeeAgreement;
use App\Models\FirmSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FeeAgreementService
{
    public function render(FeeAgreement $agreement): string
    {
        $agreement->load([
            'client.client_addresses',
            'client.client_documents',
            'client.client_contacts',
            'template',
        ]);

        $client   = $agreement->client;
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
            'firm_contract_party'   => $settings->firm_contract_party ?? '',
            'specific_text'         => strtoupper($agreement->specific_text ?? ''),
            'fee_percentage'        => $this->formatPercentage($agreement->fee_percentage),
            'city_date'             => ($settings->firm_city ?? '') . ', ' . now()->translatedFormat('d \d\e F \d\e Y'),
        ];

        $body = html_entity_decode($agreement->template->body_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Resolve placeholders no formato chip: <span data-placeholder="key">label</span>
        $body = preg_replace_callback(
            '/<span[^>]*data-placeholder="([^"]+)"[^>]*>.*?<\/span>/s',
            fn (array $matches) => $values[$matches[1]] ?? $matches[0],
            $body
        );

        // Resolve placeholders no formato simples: {{key}}
        foreach ($values as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }

    public function generate(FeeAgreement $agreement): string
    {
        $html = $agreement->rendered_body ?? $this->render($agreement);
        $firm = FirmSetting::instance();

        $logoBase64 = null;
        if ($firm->firm_logo && Storage::disk('public')->exists($firm->firm_logo)) {
            $mime       = mime_content_type(Storage::disk('public')->path($firm->firm_logo));
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($firm->firm_logo));
        }

        $pdf = Pdf::loadView('pdf.fee-agreement', [
            'body'       => $html,
            'firm'       => $firm,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4');

        $path = 'contratos/contrato-' . $agreement->id . '.pdf';

        Storage::disk('public')->makeDirectory('contratos');
        Storage::disk('public')->put($path, $pdf->output());

        $agreement->update(['pdf_path' => $path]);

        return $path;
    }

    private function formatPercentage(mixed $pct): string
    {
        $pct = (float) $pct;

        $extenso = [
            10 => 'dez', 15 => 'quinze', 20 => 'vinte',
            25 => 'vinte e cinco', 30 => 'trinta', 33 => 'trinta e três',
            35 => 'trinta e cinco', 40 => 'quarenta', 50 => 'cinquenta',
        ];

        $intPct = (int) $pct;

        if ($pct == $intPct && isset($extenso[$intPct])) {
            return $intPct . '% (' . $extenso[$intPct] . ' por cento)';
        }

        return number_format($pct, 2, ',', '.') . '%';
    }
}
