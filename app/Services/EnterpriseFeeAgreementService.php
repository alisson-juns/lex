<?php

namespace App\Services;

use App\Models\EnterpriseFeeAgreement;         // ← o tipo do argumento dos métodos
use App\Models\EnterpriseFeeAgreementTemplate; // ← usado internamente no render() para acessar $agreement->template
use App\Models\FirmSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EnterpriseFeeAgreementService
{
    public function render(EnterpriseFeeAgreement $agreement): string
    {
        $agreement->load([
            'enterprise.enterprise_addresses',
            'enterprise.enterprise_documents',
            'enterprise.enterprise_contacts',
            'representative',
            'template',
            'lawyers',
        ]);

        $enterprise     = $agreement->enterprise;
        $representative = $agreement->representative;
        $settings       = FirmSetting::instance();

        $address = collect([
            $enterprise->enterprise_addresses?->street,
            $enterprise->enterprise_addresses?->number,
            $enterprise->enterprise_addresses?->complement,
            $enterprise->enterprise_addresses?->district,
            $enterprise->enterprise_addresses?->city,
            $enterprise->enterprise_addresses?->state,
            $enterprise->enterprise_addresses?->zipcode,
        ])->filter()->join(', ');

        $values = [
            'enterprise_corporate_reason' => strtoupper($enterprise->corporate_reason ?? ''),
            'enterprise_trade_name'       => $enterprise->trade_name ?? '',
            'enterprise_cnpj'             => $enterprise->enterprise_documents?->cnpj ?? '',
            'enterprise_address'          => $address,
            'enterprise_email'            => $enterprise->enterprise_contacts?->email ?? '',
            'representative_name'         => strtoupper($representative?->name ?? ''),
            'representative_cpf'          => $representative?->cpf ?? '',
            'representative_rg'           => $representative?->rg ?? '',
            'representative_position'     => $representative?->position ?? '',
            'firm_contract_party'         => $this->buildContractParty($agreement, $settings),
            'specific_text'               => strtoupper($agreement->specific_text ?? ''),
            'fee_percentage'              => $this->formatPercentage($agreement->fee_percentage),
            'city_date'                   => ($settings->firm_city ?? '') . ', ' . now()->translatedFormat('d \d\e F \d\e Y'),
        ];

        $body = html_entity_decode($agreement->template->body_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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

    public function generate(EnterpriseFeeAgreement $agreement): string
    {
        $html = $agreement->rendered_body ?? $this->render($agreement);
        $firm = FirmSetting::instance();

        $logoBase64 = null;
        if ($firm->firm_logo && Storage::disk('public')->exists($firm->firm_logo)) {
            $mime       = mime_content_type(Storage::disk('public')->path($firm->firm_logo));
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($firm->firm_logo));
        }

        $pdf = Pdf::loadView('pdf.enterprise-fee-agreement', [
            'body'       => $html,
            'firm'       => $firm,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4');

        $path = 'contratos-pj/contrato-' . $agreement->id . '.pdf';

        Storage::disk('public')->makeDirectory('contratos-pj');
        Storage::disk('public')->put($path, $pdf->output());

        $agreement->update(['pdf_path' => $path]);

        return $path;
    }

    private function buildContractParty(EnterpriseFeeAgreement $agreement, FirmSetting $settings): string
    {
        if ($agreement->lawyers->isEmpty()) {
            return '';
        }

        $firmAddress = collect([
            $settings->firm_address,
            $settings->firm_city && $settings->firm_state
                ? $settings->firm_city . '/' . $settings->firm_state
                : ($settings->firm_city ?? $settings->firm_state ?? null),
            $settings->firm_zipcode ? 'CEP ' . $settings->firm_zipcode : null,
        ])->filter()->join(', ');

        $parts = $agreement->lawyers->map(function ($lawyer) use ($firmAddress) {
            $genero   = strtolower($lawyer->gender ?? '') === 'feminino' ? 'advogada' : 'advogado';
            $inscrito = strtolower($lawyer->gender ?? '') === 'feminino' ? 'inscrita' : 'inscrito';

            $pieces = array_filter([
                strtoupper($lawyer->name),
                $lawyer->nationality ?: null,
                $lawyer->marital_status ?: null,
                $genero,
                $lawyer->oab
                    ? $inscrito . ' na OAB/' . ($lawyer->oab_state ?? '') . ' sob o n.º ' . $lawyer->oab
                    : null,
                $firmAddress ? 'com escritório na ' . $firmAddress : null,
            ]);

            return implode(', ', $pieces);
        });

        if ($parts->count() === 1) {
            return $parts->first();
        }

        $last = $parts->pop();
        return $parts->join(', ') . ' e ' . $last;
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
