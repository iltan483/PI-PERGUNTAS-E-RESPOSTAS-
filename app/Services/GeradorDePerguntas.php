<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeradorDePerguntas
{
    private string $apiKey;
    private string $apiUrl = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.3';
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

    /**
     * Gera perguntas via Hugging Face.
     *
     * @param string $contexto
     * @param int $numPerguntasObjetivas
     * @param int $numPerguntasDissertativas
     * @return string JSON bruto da resposta gerada
     * @throws \Exception
     */
    public function gerarPerguntas(string $contexto, int $numPerguntasObjetivas = 1, int $numPerguntasDissertativas = 1): string
    {
        $prompt = $this->buildPrompt($contexto, $numPerguntasObjetivas, $numPerguntasDissertativas);

        return $this->_callIaApi($prompt);
    }

    private function buildPrompt(string $contexto, int $numObjetivas, int $numDissertativas): string
    {
        // Alteração Principal: Reestruturação do prompt
        return <<<EOT
Com base no seguinte contexto: "{$contexto}", gere {$numObjetivas} pergunta(s) objetiva(s) e {$numDissertativas} pergunta(s) dissertativa(s).

As perguntas devem seguir o formato JSON estrito abaixo.
Sua resposta deve conter APENAS o JSON e NADA MAIS.
Não inclua nenhum texto, explicação ou caracteres adicionais fora do objeto JSON.

Formato JSON esperado:
{
  "perguntas": [
    {
      "tipo": "objetiva",
      "texto_pergunta": "[Texto da pergunta objetiva]",
      "tema": "[Tema da pergunta]",
      "opcoes": [
        {"texto": "[Opção 1]", "correta": true},
        {"texto": "[Opção 2]", "correta": false},
        {"texto": "[Opção 3]", "correta": false},
        {"texto": "[Opção 4]", "correta": false}
      ],
      "resposta_modelo_conteudo": "[Texto da opção correta]",
      "resposta_modelo_tipo": "exata"
    },
    {
      "tipo": "dissertativa",
      "texto_pergunta": "[Texto da pergunta dissertativa]",
      "tema": "[Tema da pergunta]",
      "resposta_modelo_conteudo": "[Palavras-chave ou parágrafo de modelo]",
      "resposta_modelo_tipo": "palavras_chave"
    }
  ]
}
EOT;
    }

    private function _callIaApi(string $prompt): string
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


    // Tenta decodificar a resposta JSON
    $responseData = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Erro ao decodificar JSON da API Hugging Face: " . json_last_error_msg() . " Conteúdo bruto: " . $body);
    }

    // Extrai o texto gerado (diferentes modelos têm diferentes estruturas)
    if (isset($responseData[0]['generated_text'])) {
        $generatedText = $responseData[0]['generated_text'];
    } elseif (isset($responseData['generated_text'])) {
        $generatedText = $responseData['generated_text'];
    } elseif (is_string($responseData)) {
        $generatedText = $responseData;
    } else {
        throw new \Exception("Formato inesperado de resposta da API Hugging Face. Conteúdo: " . print_r($responseData, true));
    }

    // Tenta extrair o JSON embutido no texto gerado
    // Tenta encontrar todos os blocos JSON
    if (preg_match_all('/\{(?:[^{}]|(?R))*\}/s', $generatedText, $matches)) {
        // Se houver mais de um bloco JSON, pega o último (geralmente o que contém os dados reais)
        $jsonString = trim(end($matches[0]));
    } else {
        throw new \Exception("Resposta da IA não contém um bloco JSON válido. Conteúdo: " . $generatedText);
    }


    // Valida o JSON final
    $finalResponseData = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Erro ao decodificar JSON final da API Hugging Face: " . json_last_error_msg() . " Conteúdo JSON: " . $jsonString);
    }

    // Verifica se é o formato esperado
    if (!isset($finalResponseData['perguntas']) || !is_array($finalResponseData['perguntas'])) {
        throw new \Exception("Resposta da IA não contém a chave 'perguntas' esperada ou não é array.");
    }

    return $jsonString;
}

}