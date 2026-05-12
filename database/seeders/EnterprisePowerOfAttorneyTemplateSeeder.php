<?php

namespace Database\Seeders;

use App\Models\EnterprisePowerOfAttorneyTemplate;
use Illuminate\Database\Seeder;

class EnterprisePowerOfAttorneyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $intro = '<p><strong><span contenteditable="false" data-placeholder="enterprise_name" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Razão Social</span></strong>, pessoa jurídica de direito privado, inscrita no CNPJ sob o n.º <span contenteditable="false" data-placeholder="enterprise_cnpj" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">CNPJ</span>, com sede na <span contenteditable="false" data-placeholder="enterprise_address" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Endereço</span>, neste ato representada por seu representante legal <span contenteditable="false" data-placeholder="representative_name" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Nome do Representante</span>, portador(a) do CPF n.º <span contenteditable="false" data-placeholder="representative_cpf" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">CPF do Representante</span>, na qualidade de <span contenteditable="false" data-placeholder="representative_position" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Cargo/Função</span></p>';

        $mandato = '<p>Através do presente instrumento particular de mandato, nomeia e constitui como sua(s) procuradora(s) e advogada(s):</p><p><span contenteditable="false" data-placeholder="firm_lawyers" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Advogados do Escritório</span></p>';

        $clausula = '<p>Outorgando-lhe amplos poderes para o foro geral, com a cláusula <em>"ad-judicia"</em>, em qualquer JUÍZO, INSTÂNCIA ou TRIBUNAL, também de forma EXTRAJUDICIAL ou no âmbito ADMINISTRATIVO podendo propor contra quem de direito as ações competentes e defendê-las nas contrárias, seguindo umas e outras, até a final decisão, usando os recursos legais e acompanhando-os, conferindo-lhes poderes especiais para transigir, fazer acordo, firmar compromissos, renunciar, desistir, reconhecer a procedência do pedido, receber intimações, receber e dar quitação, levantar MLE, assinar acordos, e transacionar valores, podendo ainda substabelecer esta à outrem, com ou sem reserva de iguais poderes, dando tudo por bom, firme e valioso que se fizer necessário ao cumprimento do presente mandato, com o fim específico para propor <strong><span contenteditable="false" data-placeholder="specific_text" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 7px; font-size: 0.85em; display: inline-block; white-space: nowrap;">Fim Específico</span></strong></p>';

        $body = $intro . $mandato . $clausula;

        foreach (['Cível', 'Criminal', 'Trabalhista', 'INSS'] as $name) {
            EnterprisePowerOfAttorneyTemplate::firstOrCreate(
                ['name' => $name],
                ['body_text' => $body, 'is_active' => true]
            );
        }
    }
}
