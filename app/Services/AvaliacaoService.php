<?php
// app/Services/AvaliacaoService.php

namespace App\Services;

use App\Models\Pergunta;
use App\Models\OpcaoResposta;
use App\Models\RespostaModelo;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AvaliacaoService
{
    private string $apiKey;
    private string $apiUrl = 'https://api-inference.huggingface.co/models/HuggingFaceH4/zephyr-7b-beta';
    private Client $httpClient;

    public function __construct()
    {
        $apiKey = getenv('HUGGINGFACE_API_KEY');
        if (!$apiKey) {
            throw new \Exception("HUGGINGFACE_API_KEY não configurada no ambiente. Por favor, defina-a no seu arquivo .env.");
        }
        $this->apiKey = $apiKey;

        $this->httpClient = new Client([
            'timeout' => 180.0,
        ]);
    }

    public function avaliarResposta(
        Pergunta $pergunta,
        array $opcoes,
        RespostaModelo $respostaModelo,
        string $respostaUsuario
    ): array {
        $resultado = [
            'avaliacao' => 'Não avaliado',
            'pontuacao' => 0,
            'feedback' => 'Aguardando avaliação.',
            'resposta_usuario' => $respostaUsuario,
            'resposta_modelo_conteudo' => $respostaModelo->getConteudo()
        ];

        if ($pergunta->getTipoPergunta() === 'objetiva') {
            $opcaoCorreta = null;
            foreach ($opcoes as $opcao) {
                if ($opcao->isCorreta()) {
                    $opcaoCorreta = $opcao;
                    break;
                }
            }

            $respostaUsuarioTextoParaComparacao = '';
            if (is_numeric($respostaUsuario)) {
                foreach ($opcoes as $opcao) {
                    if ($opcao->getId() === (int)$respostaUsuario) {
                        $respostaUsuarioTextoParaComparacao = $opcao->getTextoOpcao();
                        break;
                    }
                }
            } else {
                $respostaUsuarioTextoParaComparacao = $respostaUsuario;
            }

            $resultado['resposta_usuario'] = htmlspecialchars($respostaUsuarioTextoParaComparacao);

            if ($opcaoCorreta && $respostaUsuarioTextoParaComparacao === $opcaoCorreta->getTextoOpcao()) {
                $resultado['avaliacao'] = 'Correta';
                $resultado['pontuacao'] = 100;
                $resultado['feedback'] = 'Excelente! Sua resposta está correta.';
            } else {
                $correta = $opcaoCorreta ? $opcaoCorreta->getTextoOpcao() : 'Não identificada';
                $resultado['avaliacao'] = 'Incorreta';
                $resultado['pontuacao'] = 0;
                $resultado['feedback'] = 'Sua resposta está incorreta. A opção correta era: ' . htmlspecialchars($correta);
            }
        } elseif ($pergunta->getTipoPergunta() === 'dissertativa') {
            try {
                $iaEvaluation = $this->avaliarDissertativaComIA(
                    $pergunta->getTextoPergunta(),
                    $respostaModelo->getConteudo(),
                    $respostaUsuario
                );

                $resultado['avaliacao'] = $iaEvaluation['avaliacao'];
                $resultado['pontuacao'] = $iaEvaluation['pontuacao'];
                $resultado['feedback'] = $iaEvaluation['feedback'];
            } catch (\Exception $e) {
                error_log("Erro na avaliação dissertativa com IA: " . $e->getMessage());
                $resultado['avaliacao'] = 'Erro na avaliação';
                $resultado['feedback'] = 'Não foi possível avaliar sua resposta no momento. Detalhes: ' . htmlspecialchars($e->getMessage());
            }
        }

        return $resultado;
    }

    private function avaliarDissertativaComIA(
        string $perguntaTexto,
        string $modeloResposta,
        string $respostaUsuario
    ): array {
        $prompt = <<<EOT
Avalie a seguinte resposta do aluno com base na pergunta e no modelo de resposta.

Pergunta: {$perguntaTexto}
Modelo de Resposta: {$modeloResposta}
Resposta do Aluno: {$respostaUsuario}

A saída DEVE ser um JSON no seguinte formato:
{
  "avaliacao": "Excelente" | "Boa" | "Parcial" | "Insuficiente",
  "pontuacao": número de 0 a 100,
  "feedback": "Comentário construtivo curto"
}

Apenas retorne o JSON, sem nenhum texto antes ou depois.
EOT;

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ];

        $data = [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 512,
                'temperature' => 0.6,
            ]
        ];

        try {
            $response = $this->httpClient->post($this->apiUrl, [
                'headers' => $headers,
                'json' => $data,
            ]);

            $body = $response->getBody()->getContents();
            $decoded = json_decode($body, true);

            if (!isset($decoded[0]['generated_text'])) {
                throw new \Exception("Resposta da Hugging Face sem texto gerado.");
            }

            $json = trim($decoded[0]['generated_text']);
            $parsed = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erro ao decodificar JSON: " . json_last_error_msg() . " JSON recebido: " . $json);
            }

            if (!isset($parsed['avaliacao'], $parsed['pontuacao'], $parsed['feedback'])) {
                throw new \Exception("JSON incompleto: falta avaliação, pontuação ou feedback. JSON: " . $json);
            }

            return $parsed;
        } catch (RequestException | \Exception $e) {
            error_log("Erro ao chamar a Hugging Face: " . $e->getMessage());
            throw new \Exception("Erro ao comunicar com a Hugging Face: " . $e->getMessage());
        }
    }
}
