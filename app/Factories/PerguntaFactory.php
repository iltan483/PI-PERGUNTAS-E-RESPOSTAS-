<?php

namespace App\Factories;

use App\Models\Pergunta;
use App\Models\OpcaoResposta;
use App\Models\RespostaModelo;

class PerguntaFactory
{
 
    public static function criarPergunta(
        string $tipoPergunta,
        string $textoPergunta,
        ?string $tema = null,
        array $opcoesRespostas = [], 
        ?string $conteudoRespostaModelo = null,
        ?string $tipoRespostaModelo = null
    ): Pergunta {
        $pergunta = new Pergunta($textoPergunta, $tipoPergunta, $tema);

        switch ($tipoPergunta) {
            case 'objetiva':
                if (empty($opcoesRespostas)) {
                    throw new \InvalidArgumentException("Perguntas objetivas requerem opções de resposta.");
                }
                if ($conteudoRespostaModelo === null || $tipoRespostaModelo === null) {
                    throw new \InvalidArgumentException("Perguntas objetivas requerem um conteúdo e tipo para o modelo de resposta.");
                }

                break;

            case 'dissertativa':
                if ($conteudoRespostaModelo === null || $tipoRespostaModelo === null) {
                    throw new \InvalidArgumentException("Perguntas dissertativas requerem um conteúdo e tipo para o modelo de resposta.");
                }
                break;

            default:
                throw new \InvalidArgumentException("Tipo de pergunta inválido fornecido à Factory.");
        }

        return $pergunta;
    }
}