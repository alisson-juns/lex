<?php

namespace Database\Seeders;

use App\Models\GratuityDeclarationTemplate;
use Illuminate\Database\Seeder;

class GratuityDeclarationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $body = '<p style="text-align:center;"><strong>DECLARAÇÃO</strong></p>'
            . '<p>Eu, <strong>{{client_name}}</strong>, {{client_nationality}}, {{client_marital_status}}, '
            . '{{client_profession}}, portador(a) da cédula de Identidade Registro Geral n.º {{client_rg}}, '
            . 'inscrito(a) no Cadastro de Pessoas Físicas do Ministério da Fazenda sob o n.º {{client_cpf}}, '
            . 'filho(a) de {{client_mother}} e {{client_father}}, nascido(a) em {{client_date_of_birth}}, '
            . 'residente e domiciliado(a) na {{client_address}}, '
            . 'com endereço eletrônico {{client_email}}.</p>'
            . '<p><strong>DECLARO PARA OS DEVIDOS FINS QUE SOU POBRE NA PURA ACEPÇÃO DA PALAVRA.</strong></p>'
            . '<p>Requeiro assim o benefício da Lei 1060/50 nos seus artigos 4º e 5º e artigo 5º, '
            . 'inciso LXXIV, da Constituição Federativa do Brasil de 1988.</p>'
            . '<p>&nbsp;</p>'
            . '<p>{{city_date}}</p>'
            . '<p>&nbsp;</p>'
            . '<table width="100%" style="border:none;">'
            . '<tr><td style="text-align:center;">'
            . '_______________________________________________<br>'
            . '<strong>{{client_name}}</strong><br>'
            . 'CPF n.º {{client_cpf}}'
            . '</td></tr>'
            . '</table>';

        GratuityDeclarationTemplate::firstOrCreate(
            ['name' => 'Gratuidade'],
            ['body_text' => $body, 'is_active' => true]
        );
    }
}
