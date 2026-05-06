<div x-data class="mb-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Campos disponíveis — clique para inserir no texto:
    </p>
    <div class="flex flex-wrap gap-2">
        @php
            $placeholders = [
                'Nome do Cliente'          => 'client_name',
                'Nacionalidade'            => 'client_nationality',
                'Estado Civil'             => 'client_marital_status',
                'Profissão'                => 'client_profession',
                'RG'                       => 'client_rg',
                'CPF'                      => 'client_cpf',
                'Nome da Mãe'              => 'client_mother',
                'Nome do Pai'              => 'client_father',
                'Data de Nascimento'       => 'client_date_of_birth',
                'Endereço Completo'        => 'client_address',
                'E-mail'                   => 'client_email',
                'Advogados do Escritório'  => 'firm_lawyers',
                'Fim Específico'           => 'specific_text',
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
    </div>
</div>

@verbatim
<script>
    const placeholderMap = {
        'client_name':          'Nome do Cliente',
        'client_nationality':   'Nacionalidade',
        'client_marital_status':'Estado Civil',
        'client_profession':    'Profissão',
        'client_rg':            'RG',
        'client_cpf':           'CPF',
        'client_mother':        'Nome da Mãe',
        'client_father':        'Nome do Pai',
        'client_date_of_birth': 'Data de Nascimento',
        'client_address':       'Endereço Completo',
        'client_email':         'E-mail',
        'firm_lawyers':         'Advogados do Escritório',
        'specific_text':        'Fim Específico',
    };

    function chipHtml(key, label) {
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
        tinymce.activeEditor.insertContent(chipHtml(key, label) + '&nbsp;');
    }

    function convertToChips(editor) {
        let content = editor.getContent();
        let changed = false;
        for (const [key, label] of Object.entries(placeholderMap)) {
            const raw = '{{' + key + '}}';
            if (content.includes(raw)) {
                content = content.split(raw).join(chipHtml(key, label));
                changed = true;
            }
        }
        if (changed) {
            editor.setContent(content);
        }
    }

    function waitForTinyMCE(attempts) {
        attempts = attempts || 0;
        if (attempts > 20) return;

        if (typeof tinymce !== 'undefined') {
            tinymce.get().forEach(function(editor) {
                convertToChips(editor);
            });
            tinymce.on('AddEditor', function(e) {
                e.editor.on('init', function() {
                    convertToChips(e.editor);
                });
            });
        } else {
            setTimeout(function() { waitForTinyMCE(attempts + 1); }, 500);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        waitForTinyMCE();
    });

    document.addEventListener('livewire:navigated', function() {
        waitForTinyMCE();
    });
</script>
@endverbatim