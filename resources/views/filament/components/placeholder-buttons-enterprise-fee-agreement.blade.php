{{-- resources/views/filament/components/placeholder-buttons-enterprise-fee-agreement.blade.php --}}
<div x-data class="mb-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Campos disponíveis — clique para inserir no texto:
    </p>
    <div class="flex flex-wrap gap-2">
        @php
            $placeholders = [
                'Razão Social'                => 'enterprise_corporate_reason',
                'Nome Fantasia'               => 'enterprise_trade_name',
                'CNPJ'                        => 'enterprise_cnpj',
                'Endereço da Empresa'         => 'enterprise_address',
                'E-mail da Empresa'           => 'enterprise_email',
                'Nome do Representante'       => 'representative_name',
                'CPF do Representante'        => 'representative_cpf',
                'RG do Representante'         => 'representative_rg',
                'Cargo do Representante'      => 'representative_position',
                'Identificação da Contratada' => 'firm_contract_party',
                'Tipo de Ação'                => 'specific_text',
                'Percentual de Honorários'    => 'fee_percentage',
                'Cidade e Data'               => 'city_date',
            ];
        @endphp

        @foreach ($placeholders as $label => $placeholder)
            <button
                type="button"
                onclick="insertPlaceholderEntFee('{{ $placeholder }}', '{{ $label }}')"
                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                       bg-primary-50 text-primary-700 border border-primary-200
                       hover:bg-primary-100 dark:bg-primary-900 dark:text-primary-300
                       dark:border-primary-700 dark:hover:bg-primary-800 transition"
            >
                {{ $label }}
            </button>
        @endforeach

        <button
            type="button"
            onclick="insertTituloEntFee()"
            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                   bg-amber-50 text-amber-700 border border-amber-200
                   hover:bg-amber-100 dark:bg-amber-900 dark:text-amber-300
                   dark:border-amber-700 dark:hover:bg-amber-800 transition"
        >
            ↑ Título
        </button>

        <button
            type="button"
            onclick="insertAssinaturaEntFee()"
            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                   bg-amber-50 text-amber-700 border border-amber-200
                   hover:bg-amber-100 dark:bg-amber-900 dark:text-amber-300
                   dark:border-amber-700 dark:hover:bg-amber-800 transition"
        >
            ✍ Bloco de Assinatura
        </button>
    </div>

    @verbatim
    <script>
        function chipHtmlEntFee(key, label) {
            return '<span contenteditable="false" data-placeholder="' + key + '" ' +
                   'style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' +
                   'border-radius:4px;padding:1px 7px;font-size:0.85em;' +
                   'display:inline-block;white-space:nowrap;">' +
                   label + '</span>';
        }

        function insertPlaceholderEntFee(key, label) {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(chipHtmlEntFee(key, label) + '&nbsp;');
        }

        function insertTituloEntFee() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) return;
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<h2 style="text-align:center;text-transform:uppercase;letter-spacing:1px;">Contrato de Honorários Advocatícios</h2>'
            );
        }

        function insertAssinaturaEntFee() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) return;
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<p>&nbsp;</p>' +
                '<p style="text-align:center;">' + chipHtmlEntFee('city_date', 'Cidade e Data') + '</p>' +
                '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">' +
                chipHtmlEntFee('enterprise_corporate_reason', 'Razão Social') + '<br>CNPJ n.º ' + chipHtmlEntFee('enterprise_cnpj', 'CNPJ') +
                '<br>Representada por: ' + chipHtmlEntFee('representative_name', 'Nome do Representante') +
                '<br><strong>CONTRATANTE</strong>' +
                '</div></td>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">' +
                '<strong>CONTRATADA</strong><br>Advogado(a)' +
                '</div></td>' +
                '</tr></table>' +
                '<p>&nbsp;</p>' +
                '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">Testemunha 1</div></td>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">Testemunha 2</div></td>' +
                '</tr></table>'
            );
        }
    </script>
    @endverbatim
