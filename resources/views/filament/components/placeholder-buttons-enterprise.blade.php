<div x-data class="mb-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Campos disponíveis — clique para inserir no texto:
    </p>
    <div class="flex flex-wrap gap-2">
        @php
            $placeholders = [
                'Razão Social'             => 'enterprise_name',
                'CNPJ'                     => 'enterprise_cnpj',
                'Endereço da Empresa'      => 'enterprise_address',
                'E-mail da Empresa'        => 'enterprise_email',
                'Nome do Representante'    => 'representative_name',
                'CPF do Representante'     => 'representative_cpf',
                'Cargo/Função'             => 'representative_position',
                'Advogados do Escritório'  => 'firm_lawyers',
                'Fim Específico'           => 'specific_text',
                'Cidade e Data'            => 'city_date',
            ];
        @endphp

        @foreach ($placeholders as $label => $placeholder)
            <button
                type="button"
                onclick="insertPlaceholder('{{ $placeholder }}', '{{ $label }}')"
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
            onclick="insertTituloEnterprise()"
            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium
                   bg-amber-50 text-amber-700 border border-amber-200
                   hover:bg-amber-100 dark:bg-amber-900 dark:text-amber-300
                   dark:border-amber-700 dark:hover:bg-amber-800 transition"
        >
            ↑ Título (PROCURAÇÃO)
        </button>

        <button
            type="button"
            onclick="insertAssinaturaEnterprise()"
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
        const placeholderMapEnterprise = {
            'enterprise_name':         'Razão Social',
            'enterprise_cnpj':         'CNPJ',
            'enterprise_address':      'Endereço da Empresa',
            'enterprise_email':        'E-mail da Empresa',
            'representative_name':     'Nome do Representante',
            'representative_cpf':      'CPF do Representante',
            'representative_position': 'Cargo/Função',
            'firm_lawyers':            'Advogados do Escritório',
            'specific_text':           'Fim Específico',
            'city_date':               'Cidade e Data',
        };

        function chipHtmlEnterprise(key, label) {
            return '<span contenteditable="false" data-placeholder="' + key + '" ' +
                   'style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' +
                   'border-radius:4px;padding:1px 7px;font-size:0.85em;' +
                   'display:inline-block;white-space:nowrap;">' +
                   label + '</span>';
        }

        function insertPlaceholder(key, label) {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(chipHtmlEnterprise(key, label) + '&nbsp;');
        }

        function insertTituloEnterprise() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<h2 style="text-align:center;text-transform:uppercase;letter-spacing:1px;">Procuração</h2>'
            );
        }

        function insertAssinaturaEnterprise() {
            if (typeof tinymce === 'undefined' || !tinymce.activeEditor) {
                alert('Clique no editor de texto antes de inserir um campo.');
                return;
            }
            tinymce.activeEditor.focus();
            tinymce.activeEditor.insertContent(
                '<p style="text-align:center;">' + chipHtmlEnterprise('city_date', 'Cidade e Data') + '</p>' +
                '<div style="width:50%;margin:40px auto 0;border-top:1px solid #111;' +
                'text-align:center;padding-top:6px;font-size:11px;">' +
                chipHtmlEnterprise('representative_name', 'Nome do Representante') + '<br>' +
                '<small>' + chipHtmlEnterprise('representative_cpf', 'CPF do Representante') + '</small>' +
                '</div>'
            );
        }

        function convertToChipsEnterprise(editor) {
            let content = editor.getContent();
            let changed = false;
            for (const [key, label] of Object.entries(placeholderMapEnterprise)) {
                const raw = '{{' + key + '}}';
                if (content.includes(raw)) {
                    content = content.split(raw).join(chipHtmlEnterprise(key, label));
                    changed = true;
                }
            }
            if (changed) {
                editor.setContent(content);
            }
        }

        function waitForTinyMCEEnterprise(attempts) {
            attempts = attempts || 0;
            if (attempts > 20) return;

            if (typeof tinymce !== 'undefined') {
                tinymce.get().forEach(function(editor) {
                    convertToChipsEnterprise(editor);
                });
                tinymce.on('AddEditor', function(e) {
                    e.editor.on('init', function() {
                        convertToChipsEnterprise(e.editor);
                    });
                });
            } else {
                setTimeout(function() { waitForTinyMCEEnterprise(attempts + 1); }, 500);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            waitForTinyMCEEnterprise();
        });

        document.addEventListener('livewire:navigated', function() {
            waitForTinyMCEEnterprise();
        });
    </script>
    @endverbatim
