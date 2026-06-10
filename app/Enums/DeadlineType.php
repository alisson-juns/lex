<?php

namespace App\Enums;

enum DeadlineType: string
{
    case Contestacao         = 'contestacao';
    case RecursoApelacao     = 'recurso_apelacao';
    case Manifestacao        = 'manifestacao';
    case EmbargosDeclaracao  = 'embargos_declaracao';
    case CumprimentoSentenca = 'cumprimento_sentenca';
    case Replica             = 'replica';
    case Contrarrazoes       = 'contrarrazoes';
    case AgravoInstrumento   = 'agravo_instrumento';
    case AlegacoesFinais     = 'alegacoes_finais';
    case EmendaInicial       = 'emenda_inicial';
    case Outro               = 'outro';

    public function label(): string
    {
        return match($this) {
            self::Contestacao         => 'Contestação',
            self::RecursoApelacao     => 'Recurso/Apelação',
            self::Manifestacao        => 'Manifestação',
            self::EmbargosDeclaracao  => 'Embargos de Declaração',
            self::CumprimentoSentenca => 'Cumprimento de Sentença',
            self::Replica             => 'Réplica',
            self::Contrarrazoes       => 'Contrarrazões',
            self::AgravoInstrumento   => 'Agravo de Instrumento',
            self::AlegacoesFinais     => 'Alegações Finais',
            self::EmendaInicial       => 'Emenda à Inicial',
            self::Outro               => 'Outro',
        };
    }
}
