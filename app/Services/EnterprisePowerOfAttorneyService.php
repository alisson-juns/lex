<?php

namespace App\Services;

use App\Models\EnterprisePowerOfAttorney;
use App\Models\FirmSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class EnterprisePowerOfAttorneyService
{
    public function render(EnterprisePowerOfAttorney $poa): string
    {
        $poa->load([
            'enterprise.enterprise_documents',
            'enterprise.enterprise_addresses',
            'enterprise.enterprise_contacts',
            'template',
            'representative',
            'lawyers',
        ]);

        $enterprise = $poa->enterprise;
        $rep        = $poa->representative;
        $settings   = FirmSetting::instance();

        $address = collect([
            $enterprise->enterprise_addresses?->street,
            $enterprise->enterprise_addresses?->number,
            $enterprise->enterprise_addresses?->complement,
            $enterprise->enterprise_addresses?->district,
            $enterprise->enterprise_addresses?->city,
            $enterprise->enterprise_addresses?->state,
            $enterprise->enterprise_addresses?->zipcode,
        ])->filter()->join(', ');

        $firmLawyers = $poa->lawyers->isNotEmpty()
            ? $poa->lawyers->map(
                fn ($l) =>
                $l->name . ($l->oab ? ' OAB ' . $l->oab . '/' . $l->oab_state : '')
            )->join(' e ')
            : ($settings->firm_lawyers ?? '');

        $values = [
            'enterprise_name'         => $enterprise->corporate_reason ?? '',
            'enterprise_cnpj'         => $enterprise->enterprise_documents?->cnpj ?? '',
            'enterprise_address'      => $address,
            'enterprise_email'        => $enterprise->enterprise_contacts?->email ?? '',
            'representative_name'     => $rep->name ?? '',
            'representative_cpf'      => $rep->cpf ?? '',
            'representative_position' => $rep->position ?? '',
            'firm_lawyers'            => $firmLawyers,
            'specific_text'           => $poa->specific_text,
            'city_date'               => ($settings->firm_city ?? '') . ', ' . now()->translatedFormat('d \d\e F \d\e Y'),
        ];

        $body = html_entity_decode($poa->template->body_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Resolve placeholders no formato de span (data-placeholder="key")
        $body = preg_replace_callback(
            '/<span[^>]*data-placeholder="([^"]+)"[^>]*>.*?<\/span>/s',
            fn (array $matches) => $values[$matches[1]] ?? $matches[0],
            $body
        );

        // Resolve placeholders no formato {{key}} (templates mais simples)
        foreach ($values as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }

    public function generate(EnterprisePowerOfAttorney $poa): string
    {
        $html = $poa->rendered_body ?? $this->render($poa);
        $firm = FirmSetting::instance();

        $logoBase64 = null;
        if ($firm->firm_logo && \Storage::disk('public')->exists($firm->firm_logo)) {
            $mime       = mime_content_type(\Storage::disk('public')->path($firm->firm_logo));
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(\Storage::disk('public')->get($firm->firm_logo));
        }

        $pdf = Pdf::loadView('pdf.enterprise-power-of-attorney', [
            'body'       => $html,
            'firm'       => $firm,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4');

        $path = 'procuracoes/empresa/procuracao-empresa-' . $poa->id . '.pdf';

        \Storage::disk('public')->put($path, $pdf->output());

        $poa->update(['pdf_path' => $path]);

        return $path;
    }
}
