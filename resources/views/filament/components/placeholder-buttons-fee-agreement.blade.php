<div x-data class="mb-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Campos disponíveis — clique para inserir no texto:
    </p>
    <div class="flex flex-wrap gap-2">
        @php
            $placeholders = [
                'Nome do Cliente'             => 'client_name',
                'Nacionalidade'               => 'client_nationality',
                'Estado Civil'                => 'client_marital_status',
                'Profissão'                   => 'client_profession',
                'RG'                          => 'client_rg',
                'CPF'                         => 'client_cpf',
                'Nome da Mãe'                 => 'client_mother',
                'Nome do Pai'                 => 'client_father',
                'Data de Nascimento'          => 'client_date_of_birth',
                'Endereço Completo'           => 'client_address',
                'E-mail'                      => 'client_email',
                'Identificação da Contratada' => 'firm_contract_party',
                'Tipo de Ação'                => 'specific_text',
                'Percentual de Honorários'    => 'fee_percentage',
                'Cidade e Data'               => 'city_date',
            ];
        @endphp

        @foreach ($placeholders as $label => $placeholder)
            <button
                type="button"
                onclick="insertPlaceholderFee('{{ $placeholder }}', '{{ $label }}')"
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
            onclick="insertTituloFee()"
            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                   bg-amber-50 text-amber-700 border border-amber-200
                   hover:bg-amber-100 dark:bg-amber-900 dark:text-amber-300
                   dark:border-amber-700 dark:hover:bg-amber-800 transition"
        >
            ↑ Título (CONTRATO DE HONORÁRIOS)
        </button>

        <button
            type="button"
            onclick="insertAssinaturaFee()"
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
        const placeholderMapFee = {
            'client_name':            'Nome do Cliente',
            'client_nationality':     'Nacionalidade',
            'client_marital_status':  'Estado Civil',
            'client_profession':      'Profissão',
            'client_rg':              'RG',
            'client_cpf':             'CPF',
            'client_mother':          'Nome da Mãe',
            'client_father':          'Nome do Pai',
            'client_date_of_birth':   'Data de Nascimento',
            'client_address':         'Endereço Completo',
            'client_email':           'E-mail',
            'firm_contract_party':    'Identificação da Contratada',
            'specific_text':          'Tipo de Ação',
            'fee_percentage':         'Percentual de Honorários',
            'city_date':              'Cidade e Data',
        };

        function chipHtmlFee(key, label) {
            return '<span contenteditable="false" data-placeholder="' + key + '" ' +
                   'style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' +
                   'border-radius:4px;padding:1px 7px;font-size:0.85em;' +
                   'display:inline-block;white-space:nowrap;">' +
                   label + '</span>';
        }

        function insertPlaceholderFee(key, label) {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(chipHtmlFee(key, label) + '&nbsp;');
        }

        function insertTituloFee() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<h2 style="text-align:center;text-transform:uppercase;letter-spacing:1px;">Contrato de Honorários Advocatícios</h2>'
            );
        }

        function insertAssinaturaFee() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<p>&nbsp;</p>' +
                '<p style="text-align:center;">' + chipHtmlFee('city_date', 'Cidade e Data') + '</p>' +
                '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">' +
                chipHtmlFee('client_name', 'Nome do Cliente') + '<br>CPF n.\u00ba ' + chipHtmlFee('client_cpf', 'CPF') + '<br><strong>CONTRATANTE</strong>' +
                '</div></td>' +
                '<td style="width:50%;text-align:center;padding:0 20px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">' +
                '<strong>CONTRATADA</strong><br>Advogada(o)' +
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