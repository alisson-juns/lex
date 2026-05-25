<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha — {{ $client->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }

        .print-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: #1e40af; color: white;
            padding: 0.5rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            z-index: 999; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .print-bar span { font-size: 0.9rem; font-weight: 600; }
        .print-bar button {
            background: white; color: #1e40af;
            border: none; border-radius: 6px;
            padding: 0.35rem 1rem; font-size: 0.85rem;
            font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.3rem;
        }
        .print-bar button:hover { background: #dbeafe; }

        .content { padding: 2cm; margin-top: 2.5rem; max-width: 900px; margin-left: auto; margin-right: auto; }

        .doc-header { border-bottom: 2px solid #1e40af; padding-bottom: 0.75rem; margin-bottom: 1.5rem; }
        .doc-header h1 { font-size: 1.3rem; color: #1e40af; }
        .doc-header p { font-size: 0.75rem; color: #6b7280; margin-top: 0.2rem; }

        .section { margin-bottom: 1.5rem; }
        .section-title {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #6b7280;
            margin-bottom: 0.6rem; padding-bottom: 0.3rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem 1.5rem; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }
        .field label { display: block; font-size: 0.65rem; color: #9ca3af; margin-bottom: 0.1rem; }
        .field span { font-size: 0.85rem; color: #111827; }

        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        th { background: #f3f4f6; text-align: left; padding: 0.35rem 0.6rem; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; }
        td { padding: 0.3rem 0.6rem; border: 1px solid #e5e7eb; color: #111827; }
        tr:nth-child(even) td { background: #f9fafb; }

        @media print {
            .print-bar { display: none !important; }
            .content { margin-top: 0; padding: 1cm; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <span>{{ $client->name }}</span>
    <button onclick="window.print()">🖨️ Imprimir</button>
</div>

<div class="content">

    <div class="doc-header">
        <h1>{{ $client->name }}</h1>
        <p>Ficha cadastral gerada em {{ now()->format('d/m/Y \à\s H:i') }}</p>
    </div>

    {{-- DADOS PESSOAIS --}}
    <div class="section">
        <div class="section-title">Dados Pessoais</div>
        <div class="grid">
            <div class="field"><label>Data de Nascimento</label><span>{{ $client->date_of_birth?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="field"><label>Gênero</label><span>{{ $client->gender ?? '—' }}</span></div>
            <div class="field"><label>Estado Civil</label><span>{{ $client->marital_status ?? '—' }}</span></div>
            <div class="field"><label>Profissão</label><span>{{ $client->profession ?? '—' }}</span></div>
            <div class="field"><label>Nacionalidade</label><span>{{ $client->nationality ?? '—' }}</span></div>
            <div class="field"><label>Naturalidade</label><span>{{ $client->place_of_birth ?? '—' }}</span></div>
            <div class="field"><label>Filiação (pai)</label><span>{{ $client->father ?? '—' }}</span></div>
            <div class="field"><label>Filiação (mãe)</label><span>{{ $client->mother ?? '—' }}</span></div>
        </div>
    </div>

    {{-- DOCUMENTOS --}}
    @if($client->client_documents)
    <div class="section">
        <div class="section-title">Documentos</div>
        <div class="grid">
            <div class="field"><label>CPF</label><span>{{ $client->client_documents->cpf ?? '—' }}</span></div>
            <div class="field"><label>RG</label><span>{{ $client->client_documents->rg ?? '—' }}</span></div>
            <div class="field"><label>CNH</label><span>{{ $client->client_documents->cnh ?? '—' }}</span></div>
            <div class="field"><label>PIS</label><span>{{ $client->client_documents->pis ?? '—' }}</span></div>
            <div class="field"><label>CTPS</label><span>{{ $client->client_documents->ctps ?? '—' }}</span></div>
            <div class="field"><label>RNM</label><span>{{ $client->client_documents->rnm ?? '—' }}</span></div>
            @if($client->client_documents->other_documents)
            <div class="field span-3"><label>Outros documentos</label><span>{{ $client->client_documents->other_documents }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- CONTATOS --}}
    @if($client->client_contacts)
    <div class="section">
        <div class="section-title">Contatos</div>
        <div class="grid">
            <div class="field"><label>E-mail</label><span>{{ $client->client_contacts->email ?? '—' }}</span></div>
            <div class="field"><label>Celular</label><span>{{ $client->client_contacts->cellphone ?? '—' }}</span></div>
            <div class="field"><label>Telefone</label><span>{{ $client->client_contacts->phone ?? '—' }}</span></div>
            @if($client->client_contacts->optional_email)
            <div class="field"><label>E-mail alternativo</label><span>{{ $client->client_contacts->optional_email }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- ENDEREÇO --}}
    @if($client->client_addresses)
    <div class="section">
        <div class="section-title">Endereço</div>
        <div class="grid">
            <div class="field span-2">
                <label>Logradouro</label>
                <span>
                    {{ $client->client_addresses->street }}, {{ $client->client_addresses->number }}
                    @if($client->client_addresses->complement) — {{ $client->client_addresses->complement }} @endif
                </span>
            </div>
            <div class="field"><label>CEP</label><span>{{ $client->client_addresses->zipcode ?? '—' }}</span></div>
            <div class="field"><label>Bairro</label><span>{{ $client->client_addresses->district ?? '—' }}</span></div>
            <div class="field"><label>Cidade</label><span>{{ $client->client_addresses->city ?? '—' }}</span></div>
            <div class="field"><label>UF</label><span>{{ $client->client_addresses->state ?? '—' }}</span></div>
        </div>
    </div>
    @endif

    {{-- DADOS BANCÁRIOS --}}
    @if($client->client_bank_accounts->count())
    <div class="section">
        <div class="section-title">Dados Bancários</div>
        <table>
            <thead>
                <tr>
                    <th>Banco</th><th>Agência</th><th>Conta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->client_bank_accounts as $account)
                <tr>
                    <td>{{ collect([$account->bank_number, $account->bank_name])->filter()->join(' — ') ?: '—' }}</td>
                    <td>{{ $account->agency ?? '—' }}</td>
                    <td>{{ $account->account ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- CÔNJUGE --}}
    @if($client->spouse)
    <div class="section">
        <div class="section-title">Cônjuge</div>
        <div class="grid">
            <div class="field"><label>Nome</label><span>{{ $client->spouse->name }}</span></div>
            <div class="field"><label>CPF</label><span>{{ $client->spouse->cpf ?? '—' }}</span></div>
            <div class="field"><label>RG</label><span>{{ $client->spouse->rg ?? '—' }}</span></div>
            <div class="field"><label>Profissão</label><span>{{ $client->spouse->profession ?? '—' }}</span></div>
            <div class="field"><label>Data de Nascimento</label><span>{{ $client->spouse->date_of_birth?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="field"><label>Celular</label><span>{{ $client->spouse->mobile ?? '—' }}</span></div>
        </div>
    </div>
    @endif

    {{-- DEPENDENTES --}}
    @if($client->wards->count())
    <div class="section">
        <div class="section-title">Dependentes ({{ $client->wards->count() }})</div>
        <table>
            <thead>
                <tr><th>Nome</th><th>CPF</th><th>RG</th><th>Nascimento</th></tr>
            </thead>
            <tbody>
                @foreach($client->wards as $ward)
                <tr>
                    <td>{{ $ward->name }}</td>
                    <td>{{ $ward->cpf ?? '—' }}</td>
                    <td>{{ $ward->rg ?? '—' }}</td>
                    <td>{{ $ward->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- PROCESSOS --}}
    @if($client->legalCases->count())
    <div class="section">
        <div class="section-title">Processos ({{ $client->legalCases->count() }})</div>
        <table>
            <thead>
                <tr><th>Nº Processo</th><th>Pasta</th><th>Adverso</th><th>Advogado(s)</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($client->legalCases as $case)
                <tr>
                    <td>{{ $case->case_number ?? '—' }}</td>
                    <td>{{ $case->folder_number ?? '—' }}</td>
                    <td>{{ $case->opponent_name ?? '—' }}</td>
                    <td>{{ $case->lawyers->pluck('name')->join(', ') ?: '—' }}</td>
                    <td>{{ $case->status->label() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- OBSERVAÇÕES --}}
    @if($client->note)
    <div class="section">
        <div class="section-title">Observações</div>
        <p style="font-size: 0.85rem; color: #374151; line-height: 1.5;">{{ $client->note }}</p>
    </div>
    @endif

</div>
</body>
</html>