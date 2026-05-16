<?php

namespace Database\Seeders;

use App\Models\FeeAgreementTemplate;
use Illuminate\Database\Seeder;

class FeeAgreementTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ── Partes fixas do contrato ──────────────────────────────────────────

        $contratadas = '<p>Pelo presente Instrumento Particular de Contrato de Honorários Advocatícios, de um lado, '
            . '<strong>{{firm_contract_party}}</strong>, '
            . 'de agora em diante denominada <strong>CONTRATADA</strong>, e de outro lado, '
            . '<strong>{{client_name}}</strong>, {{client_nationality}}, {{client_marital_status}}, '
            . '{{client_profession}}, portador(a) da cédula de Identidade Registro Geral n.º {{client_rg}}, '
            . 'inscrito(a) no Cadastro de Pessoas Físicas do Ministério da Fazenda sob o n.º {{client_cpf}}, '
            . 'filho(a) de {{client_mother}} e {{client_father}}, nascido(a) em {{client_date_of_birth}}, '
            . 'residente e domiciliado(a) na {{client_address}}, com endereço eletrônico {{client_email}}, '
            . 'de agora em diante denominado(a) <strong>CONTRATANTE</strong>, que convenciona e contrata o seguinte:</p>';

        $clausula1 = '<p>- A <strong>CONTRATADA</strong> obriga-se, face ao mandato que lhe é outorgado, cujo qual faz parte integrante deste contrato, '
            . 'a prestar seus serviços profissionais, em defesa do <strong>CONTRATANTE</strong>, praticando todos os atos necessários, '
            . 'em juízo ou de forma administrativa com a finalidade de propor <strong>{{specific_text}}</strong>.</p>';

        $clausula2 = '<p>- Em remuneração aos serviços profissionais a serem prestados, o(a) <strong>CONTRATANTE</strong> '
            . 'concorda em pagar à <strong>CONTRATADA</strong> a título de honorários advocatícios, '
            . 'a importância de <strong>{{fee_percentage}}</strong> do valor auferido ao final da ação ou do acordo judicial/extrajudicial.</p>'
            . '<p><strong>Parágrafo primeiro.</strong> O não cumprimento da obrigação prevista nesta cláusula, na data prevista, '
            . 'importará em multa de 10% (dez por cento), e juros de 1% ao mês, bem como a atualização do valor total '
            . 'devido pela Tabela Prática do TJSP.</p>'
            . '<p><strong>Parágrafo segundo.</strong> Os honorários poderão ser majorados em decorrência do aumento dos atos judiciais '
            . 'que advirem como necessários, desde que a <strong>CONTRATADA</strong> comunique ao <strong>CONTRATANTE</strong> por escrito.</p>';

        $clausula3 = '<p>- Fica estabelecido que, iniciados os serviços especificados na cláusula 1ª, serão devidos os honorários '
            . 'contratados por completo neste instrumento, adicionado de multa no valor de três salários mínimos vigentes, '
            . 'ainda que em caso de desistência por parte do <strong>CONTRATANTE</strong>, ou em caso de não comparecimento aos atos '
            . 'processuais, ou se for cassado o mandato outorgado à <strong>CONTRATADA</strong>, ou por acordo do '
            . '<strong>CONTRATANTE</strong> com a parte contrária sem a devida aquiescência da <strong>CONTRATADA</strong>.</p>';

        $clausula4 = '<p>- Os honorários pactuados abrangem apenas a remuneração dos serviços prestados pela <strong>CONTRATADA</strong>, '
            . 'sendo certo que eventuais despesas judiciais, extrajudiciais ou outras correrão por conta do '
            . '<strong>CONTRATANTE</strong>.</p>'
            . '<p><strong>Parágrafo único.</strong> Todas as despesas judiciais (custas, emolumentos, honorários periciais, preparos, '
            . 'taxas, etc.) serão suportadas pelo <strong>CONTRATANTE</strong>, antecipadamente ou com posterior comprovação.</p>';

        $clausula5 = '<p>- Sendo a atividade da <strong>CONTRATADA</strong> <em>atividade de meio e não de resultado</em>, '
            . 'fica estabelecido que os honorários serão sempre devidos, independentemente do resultado da ação. '
            . 'Em caso de saída vencedora do <strong>CONTRATANTE</strong>, os honorários de sucumbência pertencerão, '
            . 'única e exclusivamente, à <strong>CONTRATADA</strong>, nos termos do art. 23 do EOAB, Lei 8.906/94.</p>';

        $clausula6 = '<p>- O <strong>CONTRATANTE</strong> obriga-se a fornecer todos os documentos e informações solicitados '
            . 'pela <strong>CONTRATADA</strong> no menor tempo possível, correndo sob sua responsabilidade o prejuízo '
            . 'que advir da ausência da entrega da documentação.</p>';

        $clausula7 = '<p>- Obriga-se o <strong>CONTRATANTE</strong> a participar e comparecer em todos os atos processuais '
            . 'a que vier ser necessária sua presença, ficando ciente de que se faltar ao ato processual em que for '
            . 'devidamente avisado, deverá ser responsabilizado pelo resultado do processo.</p>';

        $clausula8 = '<p>- A <strong>CONTRATADA</strong>, bem como seus prepostos, segundo seu entendimento, buscando o '
            . 'melhor deslinde, estão autorizados a desistir da interposição de recursos cabíveis.</p>';

        $clausulaforo = '<p>- Fica eleito o Foro da Comarca da sede da <strong>CONTRATADA</strong> para dirimir quaisquer '
            . 'dúvidas decorrentes da execução deste contrato, com exclusão de qualquer outro, por mais privilegiado que seja.</p>';

        $assinatura = '<p>E assim, estarem justos e contratados, assinam o presente em duas vias de igual teor e rubricadas '
            . 'nos anversos de cada página, na presença de duas testemunhas.</p>'
            . '<br>'
            . '<p>{{city_date}}</p>'
            . '<br><br>'
            . '<table width="100%" style="border:none;">'
            . '<tr>'
            . '<td width="50%" style="text-align:center;"><strong>{{client_name}}</strong><br>CPF n.º {{client_cpf}}<br>___________________________________<br>CONTRATANTE</td>'
            . '<td width="50%" style="text-align:center;"><strong>CONTRATADA</strong><br>___________________________________<br>Advogada</td>'
            . '</tr>'
            . '</table>'
            . '<br>'
            . '<table width="100%" style="border:none;">'
            . '<tr>'
            . '<td width="50%" style="text-align:center;">___________________________________<br>Testemunha 1</td>'
            . '<td width="50%" style="text-align:center;">___________________________________<br>Testemunha 2</td>'
            . '</tr>'
            . '</table>';

        // ── Corpo completo ────────────────────────────────────────────────────
        $body = $contratadas
            . $clausula1
            . $clausula2
            . $clausula3
            . $clausula4
            . $clausula5
            . $clausula6
            . $clausula7
            . $clausula8
            . $clausulaforo
            . $assinatura;

        // ── Cria os modelos ───────────────────────────────────────────────────
        $templates = [
            'Trabalhista',
            'Cível',
            'Criminal',
            'INSS',
            'Família',
        ];

        foreach ($templates as $name) {
            FeeAgreementTemplate::firstOrCreate(
                ['name' => $name],
                ['body_text' => $body, 'is_active' => true]
            );
        }
    }
}
