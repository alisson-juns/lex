<?php

namespace Database\Seeders;

use App\Models\PowerOfAttorneyTemplate;
use Illuminate\Database\Seeder;

class PowerOfAttorneyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $clausula = 'Outorgando-lhe amplos poderes para o foro geral, com a cláusula <em>"ad-judicia"</em>, em qualquer JUÍZO, INSTÂNCIA ou TRIBUNAL, também de forma EXTRAJUDICIAL ou no âmbito ADMINISTRATIVO podendo propor contra quem de direito as ações competentes e defendê-las nas contrárias, seguindo umas e outras, até a final decisão, usando os recursos legais e acompanhando-os, conferindo-lhes poderes especiais para transigir, fazer acordo, firmar compromissos, renunciar, desistir, reconhecer a procedência do pedido, receber intimações, receber e dar quitação, levantar MLE, assinar acordos, e transacionar valores, abrir, encerrar, fazer atualização cadastral de empresas em seu nome, podendo atuar em conjunto ou separadamente, podendo ainda substabelecer esta à outrem, com ou sem reserva de iguais poderes, dando tudo por bom, firme e valioso que se fizer necessário ao cumprimento do presente mandato, com o fim específico para propor <strong>{{specific_text}}</strong>';

        $intro = '<p><strong>{{client_name}}</strong>, {{client_nationality}}, {{client_marital_status}}, {{client_profession}}, portador(a) da cédula de Identidade Registro Geral n.º {{client_rg}}, inscrito(a) no CPF n.º {{client_cpf}}, filho(a) de {{client_mother}} e {{client_father}}, nascido(a) em {{client_date_of_birth}}, residente e domiciliado(a) na {{client_address}}, com endereço eletrônico {{client_email}}</p>';

        $mandato = '<p>Através do presente instrumento particular de mandato, nomeia e constitui como sua(s) procuradora(s) e advogada(s):</p><p>{{firm_lawyers}}</p>';

        $templates = [
            [
                'name' => 'Cível',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
            [
                'name' => 'Criminal',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
            [
                'name' => 'Trabalhista',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
            [
                'name' => 'INSS',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
            [
                'name' => 'Menor',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
            [
                'name' => 'Declaração de Pobreza',
                'body_text' => $intro . $mandato . '<p>' . $clausula . '</p>',
            ],
        ];

        foreach ($templates as $template) {
            PowerOfAttorneyTemplate::firstOrCreate(
                ['name' => $template['name']],
                ['body_text' => $template['body_text'], 'is_active' => true]
            );
        }
    }
}