<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha — {{ $enterprise->corporate_reason }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }

        .print-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: #065f46; color: white;
            padding: 0.5rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            z-index: 999; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .print-bar span { font-size: 0.9rem; font-weight: 600; }
        .print-bar button {
            background: white; color: #065f46;
            border: none; border-radius: 6px;
            padding: 0.35rem 1rem; font-size: 0.85rem;
            font-weight: 700; cursor: pointer;
        }
        .print-bar button:hover { background: #d1fae5; }

        .content { padding: 2cm; margin-top: 2.5rem; max-width: 900px; margin-left: auto; margin-right: auto; }

        .doc-header { border-bottom: 2px solid #065f46; padding-bottom: 0.75rem; margin-bottom: 1.5rem; }
        .doc-header h1 { font-size: 1.3rem; color: #065f46; }
        .doc-header h2 { font-size: 0.95rem; color: #374151; font-weight: normal; margin-top: 0.2rem; }
        .doc-header p { font-size: 0.75rem; color: #6b7280; margin-top: 0.2rem; }

        .section { margin-bottom: 1.5rem; }
        .section-title {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #6b7280;
            margin-bottom: 0.6rem; padding-bottom: 0.3rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem 1.5rem; }
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
    <span>{{ $enterprise->corporate_reason }}</span>
    <button onclick="window.print()">🖨️ Imprimir</button>
</div>

<div class="content">

    <div class="doc-header">
        <h1>{{ $enterprise->corporate_reason }}</h1>
        @if($enterprise->trade_name)
        <h2>Nome fantasia: {{ $enterprise->trade_name }}</h2>
        @endif
        <p>Ficha cadastral gerada em {{ now()->format('d/m/Y \à\s H:i') }}</p>
    </div>

    {{-- DOCUMENTOS --}}
    @if($enterprise->enterprise_documents)
    <div class="section">
        <div class="section-title">Documentos</div>
        <div class="grid">
            <div class="field"><label>CNPJ</label><span>{{ $enterprise->enterprise_documents->cnpj ?? '—' }}</span></div>
            <div class="field"><label>Inscrição Estadual</label><span>{{ $enterprise->enterprise_documents->ie ?? '—' }}</span></div>
            <div class="field"><label>Inscrição Municipal</label><span>{{ $enterprise->enterprise_documents->im ?? '—' }}</span></div>
            @if($enterprise->enterprise_documents->other_documents)
            <div class="field span-3"><label>Outros documentos</label><span>{{ $enterprise->enterprise_documents->other_documents }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- CONTATOS --}}
    @if($enterprise->enterprise_contacts)
    <div class="section">
        <div class="section-title">Contatos</div>
        <div class="grid">
            <div class="field"><label>E-mail</label><span>{{ $enterprise->enterprise_contacts->email ?? '—' }}</span></div>
            <div class="field"><label>Celular</label><span>{{ $enterprise->enterprise_contacts->cellphone ?? '—' }}</span></div>
            <div class="field"><label>Telefone</label><span>{{ $enterprise->enterprise_contacts->phone ?? '—' }}</span></div>
            @if($enterprise->enterprise_contacts->optional_email)
            <div class="field"><label>E-mail alternativo</label><span>{{ $enterprise->enterprise_contacts->optional_email }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- ENDEREÇO --}}
    @if($enterprise->enterprise_addresses)
    <div class="section">
        <div class="section-title">Endereço</div>
        <div class="grid">
            <div class="field span-2">
                <label>Logradouro</label>
                <span>
                    {{ $enterprise->enterprise_addresses->street }}, {{ $enterprise->enterprise_addresses->number }}
                    @if($enterprise->enterprise_addresses->complement) — {{ $enterprise->enterprise_addresses->complement }} @endif
                </span>
            </div>
            <div class="field"><label>CEP</label><span>{{ $enterprise->enterprise_addresses->zipcode ?? '—' }}</span></div>
            <div class="field"><label>Bairro</label><span>{{ $enterprise->enterprise_addresses->district ?? '—' }}</span></div>
            <div class="field"><label>Cidade</label><span>{{ $enterprise->enterprise_addresses->city ?? '—' }}</span></div>
            <div class="field"><label>UF</label><span>{{ $enterprise->enterprise_addresses->state ?? '—' }}</span></div>
        </div>
    </div>
    @endif

    {{-- DADOS BANCÁRIOS --}}
    @if($enterprise->enterprise_bank_accounts)
    <div class="section">
        <div class="section-title">Dados Bancários</div>
        <div class="grid">
            <div class="field"><label>Banco</label><span>{{ collect([$enterprise->enterprise_bank_accounts->bank_number, $enterprise->enterprise_bank_accounts->bank_name])->filter()->join(' — ') ?: '—' }}</span></div>
            <div class="field"><label>Agência</label><span>{{ $enterprise->enterprise_bank_accounts->agency ?? '—' }}</span></div>
            <div class="field"><label>Conta</label><span>{{ $enterprise->enterprise_bank_accounts->account ?? '—' }}</span></div>
        </div>
    </div>
    @endif

    {{-- REPRESENTANTES --}}
    @if($enterprise->enterprise_representatives->count())
    <div class="section">
        <div class="section-title">Representantes ({{ $enterprise->enterprise_representatives->count() }})</div>
        <table>
            <thead>
                <tr><th>Nome</th><th>Cargo</th><th>CPF</th><th>E-mail</th><th>Telefone</th></tr>
            </thead>
            <tbody>
                @foreach($enterprise->enterprise_representatives as $rep)
                <tr>
                    <td>{{ $rep->name }}</td>
                    <td>{{ $rep->position ?? '—' }}</td>
                    <td>{{ $rep->cpf ?? '—' }}</td>
                    <td>{{ $rep->email ?? '—' }}</td>
                    <td>{{ $rep->phone ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- PROCESSOS --}}
    @if($enterprise->legalCases->count())
    <div class="section">
        <div class="section-title">Processos ({{ $enterprise->legalCases->count() }})</div>
        <table>
            <thead>
                <tr><th>Nº Processo</th><th>Pasta</th><th>Adverso</th><th>Advogado(s)</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($enterprise->legalCases as $case)
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
    @if($enterprise->note)
    <div class="section">
        <div class="section-title">Observações</div>
        <p style="font-size: 0.85rem; color: #374151; line-height: 1.5;">{{ $enterprise->note }}</p>
    </div>
    @endif

</div>
</body>
</html>