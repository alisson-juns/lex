<?php

namespace App\Services;

use App\Models\FirmSetting;
use App\Models\PowerOfAttorney;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class PowerOfAttorneyService
{
    public function generate(PowerOfAttorney $poa): \Barryvdh\DomPDF\PDF
{
    $poa->load(['client.client_documents', 'client.client_addresses', 'client.client_contacts', 'template']);

    $client  = $poa->client;
    $docs    = $client->client_documents;
    $address = $client->client_addresses;
    $contact = $client->client_contacts;
    $firm    = FirmSetting::instance();

    $logoBase64 = null;
    if ($firm->firm_logo && file_exists(storage_path('app/public/' . $firm->firm_logo))) {
        $logoData   = file_get_contents(storage_path('app/public/' . $firm->firm_logo));
        $logoMime   = mime_content_type(storage_path('app/public/' . $firm->firm_logo));
        $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
    }

    $fullAddress = collect([
        $address?->street,
        $address?->number,
        $address?->complement,
        $address?->district,
        $address?->city,
        $address?->state,
        $address?->zipcode ? 'CEP: ' . $address->zipcode : null,
    ])->filter()->join(', ');

            $placeholders = [
                '{{client_name}}'           => $client->name ?? '',
                '{{client_nationality}}'    => $client->nationality ?? '',
                '{{client_marital_status}}' => $this->maritalStatus($client->marital_status),
                '{{client_profession}}'     => $client->profession ?? '',
                '{{client_rg}}'             => $docs?->rg ?? '',
                '{{client_cpf}}'            => $docs?->cpf ?? '',
                '{{client_mother}}'         => $client->mother ?? '',
                '{{client_father}}'         => $client->father ?? '',
                '{{client_date_of_birth}}'  => $client->date_of_birth
                    ? Carbon::parse($client->date_of_birth)->format('d/m/Y')
                    : '',
                '{{client_address}}'        => $fullAddress,
                '{{client_email}}'          => $contact?->email ?? '',
                '{{firm_lawyers}}'          => $firm->firm_lawyers ?? '',
                '{{specific_text}}'         => $poa->specific_text,
            ];
            
            // Converte chips <span data-placeholder="client_name"> de volta para {{client_name}}
            $bodyRaw = preg_replace(
                '/<span\b[^>]*\bdata-placeholder="([^"]+)"[^>]*>.*?<\/span>/s',
                '{{$1}}',
                $poa->template->body_text
            );
            
            $body = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $bodyRaw
            );

    // ... resto do método continua igual

        $currentDate = $this->dateByExtension(now()->toDateString());
        $firmCity    = $firm->firm_city ?? 'Santos';

        $pdf = Pdf::loadView('pdf.power-of-attorney', compact(
            'body', 'firm', 'logoBase64', 'currentDate', 'firmCity'
        ))->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function maritalStatus(?string $status): string
    {
        return match ($status) {
            'single'    => 'solteiro(a)',
            'married'   => 'casado(a)',
            'separated' => 'separado(a)',
            'divorced'  => 'divorciado(a)',
            'widowed'   => 'viúvo(a)',
            default     => '',
        };
    }

    private function dateByExtension(string $date): string
    {
        $months = [
            1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
            'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro',
        ];
        $dt = Carbon::parse($date);
        return $dt->day . ' de ' . $months[$dt->month] . ' de ' . $dt->year;
    }
}