{{-- resources/views/filament/components/placeholder-buttons-gratuity-declaration.blade.php --}}
<div x-data class="mb-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Campos disponíveis — clique para inserir no texto:
    </p>
    <div class="flex flex-wrap gap-2">
        @php
            $placeholders = [
                'Nome do Cliente'    => 'client_name',
                'Nacionalidade'      => 'client_nationality',
                'Estado Civil'       => 'client_marital_status',
                'Profissão'          => 'client_profession',
                'RG'                 => 'client_rg',
                'CPF'                => 'client_cpf',
                'Nome da Mãe'        => 'client_mother',
                'Nome do Pai'        => 'client_father',
                'Data de Nascimento' => 'client_date_of_birth',
                'Endereço Completo'  => 'client_address',
                'E-mail'             => 'client_email',
                'Cidade e Data'      => 'city_date',
            ];
        @endphp

        @foreach ($placeholders as $label => $placeholder)
            <button
                type="button"
                onclick="insertPlaceholderGD('{{ $placeholder }}', '{{ $label }}')"
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
            onclick="insertTituloGD()"
            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                   bg-amber-50 text-amber-700 border border-amber-200
                   hover:bg-amber-100 dark:bg-amber-900 dark:text-amber-300
                   dark:border-amber-700 dark:hover:bg-amber-800 transition"
        >
            ↑ Título
        </button>

        <button
            type="button"
            onclick="insertAssinaturaGD()"
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
        function chipHtmlGD(key, label) {
            return '<span contenteditable="false" data-placeholder="' + key + '" ' +
                   'style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' +
                   'border-radius:4px;padding:1px 7px;font-size:0.85em;' +
                   'display:inline-block;white-space:nowrap;">' +
                   label + '</span>';
        }

        function insertPlaceholderGD(key, label) {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(chipHtmlGD(key, label) + '&nbsp;');
        }

        function insertTituloGD() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) return;
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<h2 style="text-align:center;text-transform:uppercase;letter-spacing:1px;">Declaração de Pobreza</h2>'
            );
        }

        function insertAssinaturaGD() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) return;
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<p>&nbsp;</p>' +
                '<p style="text-align:center;">' + chipHtmlGD('city_date', 'Cidade e Data') + '</p>' +
                '<p>&nbsp;</p>' +
                '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
                '<td style="text-align:center;padding:0 40px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;">' +
                chipHtmlGD('client_name', 'Nome do Cliente') +
                '<br>CPF n.º ' + chipHtmlGD('client_cpf', 'CPF') +
                '</div></td>' +
                '</tr></table>'
            );
        }
    </script>
    @endverbatim
