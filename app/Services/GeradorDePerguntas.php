<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeradorDePerguntas
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

    public function gerarPerguntas(string $contexto, int $numPerguntasObjetivas = 1, int $numPerguntasDissertativas = 1): string
    {
        $prompt = $this->buildPrompt($contexto, $numPerguntasObjetivas, $numPerguntasDissertativas);
        return $this->_callIaApi($prompt, $numPerguntasObjetivas, $numPerguntasDissertativas);
    }

    private function buildPrompt(string $contexto, int $numObjetivas, int $numDissertativas): string
    {
        return <<<EOT
Você é um gerador de provas. Crie exatamente $numObjetivas pergunta(s) objetivas e $numDissertativas pergunta(s) dissertativas com base no seguinte contexto:

"$contexto"

A saída deve ser APENAS um JSON com este formato:

{
  "perguntas": [
    {
      "tipo": "objetiva",
      "texto_pergunta": "Qual é a capital do Brasil?",
      "tema": "Geografia",
      "opcoes": [
        {"texto": "Brasília", "correta": true},
        {"texto": "Rio de Janeiro", "correta": false},
        {"texto": "São Paulo", "correta": false},
        {"texto": "Salvador", "correta": false}
      ],
      "resposta_modelo_conteudo": "Brasília",
      "resposta_modelo_tipo": "exata"
    },
    {
      "tipo": "dissertativa",
      "texto_pergunta": "Explique o conceito de biodiversidade.",
      "tema": "Biologia",
      "resposta_modelo_conteudo": "Diversidade de espécies, variação genética e ecossistemas.",
      "resposta_modelo_tipo": "palavras_chave"
    }
  ]
}
EOT;
    }

    private function _callIaApi(string $prompt, int $numObjetivas, int $numDissertativas): string
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 1024,
                'temperature' => 0.5,
                'do_sample' => true,
            ],
        ];

        try {
            $response = $this->httpClient->post($this->apiUrl, [
                'headers' => $headers,
                'json' => $data,
            ]);
        } catch (RequestException $e) {
            $resposta = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A';
            error_log("Erro na requisição Hugging Face: " . $e->getMessage() . " Resposta: " . $resposta);
            throw new \Exception("Erro ao comunicar com a API Hugging Face: " . $e->getMessage());
        }

        $body = $response->getBody()->getContents();

        $responseData = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON da resposta bruta: " . json_last_error_msg());
        }

        if (isset($responseData[0]['generated_text'])) {
            $generatedText = $responseData[0]['generated_text'];
        } elseif (isset($responseData['generated_text'])) {
            $generatedText = $responseData['generated_text'];
        } else {
            throw new \Exception("Formato inesperado da resposta da API. Conteúdo: " . print_r($responseData, true));
        }

        if (preg_match_all('/\{(?:[^{}]|(?R))*\}/s', $generatedText, $matches)) {
            $jsonString = trim(end($matches[0]));

            if (str_starts_with($jsonString, '"{') || str_contains($jsonString, '\\"')) {
                $jsonString = stripslashes(trim($jsonString, '"'));
            }
        } else {
            throw new \Exception("Resposta da IA não contém JSON válido. Conteúdo: " . $generatedText);
        }

        $finalResponseData = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON final: " . json_last_error_msg() . " Conteúdo JSON: " . $jsonString);
        }

        if (!isset($finalResponseData['perguntas']) || !is_array($finalResponseData['perguntas'])) {
            throw new \Exception("JSON final não contém a chave 'perguntas' ou não é array.");
        }

        // Filtro para garantir a quantidade correta
        $objetivas = array_filter($finalResponseData['perguntas'], fn($p) => $p['tipo'] === 'objetiva');
        $dissertativas = array_filter($finalResponseData['perguntas'], fn($p) => $p['tipo'] === 'dissertativa');

        $objetivas = array_slice($objetivas, 0, $numObjetivas);
        $dissertativas = array_slice($dissertativas, 0, $numDissertativas);

        $finalResponseData['perguntas'] = array_merge($objetivas, $dissertativas);

        return json_encode($finalResponseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
