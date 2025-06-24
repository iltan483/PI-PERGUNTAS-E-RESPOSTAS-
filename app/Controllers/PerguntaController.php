<?php

namespace App\Controllers;

use App\Services\GeradorDePerguntas;
use App\Services\PerguntaService;
use App\Services\AvaliacaoService;
use App\Models\RespostaUsuario; 

class PerguntaController
{
    private GeradorDePerguntas $geradorDePerguntas;
    private PerguntaService $perguntaService;
    private AvaliacaoService $avaliacaoService;

    public function __construct()
    {
        $this->geradorDePerguntas = new GeradorDePerguntas();
        $this->perguntaService = new PerguntaService();
        $this->avaliacaoService = new AvaliacaoService();
    }

    public function gerarForm()
    {
        require_once __DIR__ . '/../Views/form_gerar_perguntas.php';
    }

    public function gerar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contexto = $_POST['contexto'] ?? '';
            $numObjetivas = (int)($_POST['num_objetivas'] ?? 1);
            $numDissertativas = (int)($_POST['num_dissertativas'] ?? 1);

            if (empty($contexto)) {
                $mensagemErro = "Por favor, forneça um contexto para gerar as perguntas.";
                require_once __DIR__ . '/../Views/form_gerar_perguntas.php';
                return;
            }

            try {
                $jsonGerado = $this->geradorDePerguntas->gerarPerguntas(
                $contexto,
                $numObjetivas,
                $numDissertativas
            );

            $dados = json_decode($jsonGerado, true);

            if (!isset($dados['perguntas']) || !is_array($dados['perguntas'])) {
                throw new \Exception("Resposta da IA não contém a chave 'perguntas' esperada ou ela não é um array.");
            }

            $perguntasGeradas = $dados['perguntas'];
            $perguntasPersistidas = [];

            foreach ($perguntasGeradas as $perguntaGerada) {
                $tipo = $perguntaGerada['tipo'] ?? null;
                $texto = $perguntaGerada['texto_pergunta'] ?? null;
                $tema = $perguntaGerada['tema'] ?? null;
                $opcoes = $perguntaGerada['opcoes'] ?? [];
                $modeloConteudo = $perguntaGerada['resposta_modelo_conteudo'] ?? null;
                $modeloTipo = $perguntaGerada['resposta_modelo_tipo'] ?? null;

                // Validação mínima (opcional, mas recomendado)
                if (!$tipo || !$texto || !$tema) {
                    continue; // Ignora perguntas malformadas
                }

                $perguntaPersistida = $this->perguntaService->criarNovaPerguntaComDetalhes(
                    $tipo,
                    $texto,
                    $tema,
                    $opcoes,
                    $modeloConteudo,
                    $modeloTipo
                );

                if ($perguntaPersistida) {
                    $perguntasPersistidas[] = $perguntaPersistida;
                }
            }

            $mensagemSucesso = "Perguntas geradas e salvas com sucesso!";
            $perguntas = $perguntasPersistidas;
            require_once __DIR__ . '/../Views/lista_perguntas.php';



            } catch (\Exception $e) {
                $mensagemErro = "Erro ao gerar perguntas: " . $e->getMessage();
                error_log("Erro na geração de perguntas: " . $e->getMessage());
                require_once __DIR__ . '/../Views/form_gerar_perguntas.php';
            }
        } else {
            $this->gerarForm();
        }
    }


    public function listar()
    {
        $perguntas = $this->perguntaService->buscarTodasPerguntas();
        require_once __DIR__ . '/../Views/lista_perguntas.php';
    }

    public function detalhes(int $id)
    {
        $dadosPerguntaCompleta = $this->perguntaService->buscarPerguntaCompleta($id);
        if (!$dadosPerguntaCompleta) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1><p>Pergunta não encontrada.</p>";
            return;
        }
        $pergunta = $dadosPerguntaCompleta['pergunta'];
        $opcoes = $dadosPerguntaCompleta['opcoes'];
        $respostaModelo = $dadosPerguntaCompleta['resposta_modelo'];

        require_once __DIR__ . '/../Views/detalhe_pergunta.php';
    }

    public function exibirFormularioResposta(int $id)
    {
        $dadosPerguntaCompleta = $this->perguntaService->buscarPerguntaCompleta($id);

        if (!$dadosPerguntaCompleta || !$dadosPerguntaCompleta['pergunta']) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1><p>Pergunta não encontrada para responder.</p>";
            return;
        }

        $pergunta = $dadosPerguntaCompleta['pergunta'];
        $opcoes = $dadosPerguntaCompleta['opcoes'];
        $respostaModelo = $dadosPerguntaCompleta['resposta_modelo'];

        require_once __DIR__ . '/../Views/responder_pergunta.php';
    }

    public function processarResposta(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $respostaTexto = $_POST['resposta_usuario'] ?? '';

            if (empty($respostaTexto)) {
                $mensagemErro = "Por favor, forneça uma resposta.";
                $this->exibirFormularioResposta($id);
                return;
            }

            $dadosPerguntaCompleta = $this->perguntaService->buscarPerguntaCompleta($id);
            if (!$dadosPerguntaCompleta || !$dadosPerguntaCompleta['pergunta']) {
                $mensagemErro = "Pergunta não encontrada para processar a resposta.";
                header("HTTP/1.0 404 Not Found");
                echo "<h1>404 Not Found</h1><p>Pergunta não encontrada.</p>";
                return;
            }

            $pergunta = $dadosPerguntaCompleta['pergunta'];
            $respostaModelo = $dadosPerguntaCompleta['resposta_modelo'];
            $opcoes = $dadosPerguntaCompleta['opcoes'];

            try {

                $userId = 1; 
                $respostaUsuario = new RespostaUsuario(
                    $pergunta->getId(),
                    $userId,
                    $respostaTexto
                );

                $respostaUsuarioId = $this->perguntaService->salvarRespostaUsuario($respostaUsuario);

                if (!$respostaUsuarioId) {
                    throw new \Exception("Falha ao salvar a resposta do usuário.");
                }

                $resultadoAvaliacao = $this->avaliacaoService->avaliarResposta(
                    $pergunta,
                    $opcoes, 
                    $respostaModelo,
                    $respostaTexto
                );

                $mensagemSucesso = "Sua resposta foi enviada e avaliada!";
                require_once __DIR__ . '/../Views/responder_pergunta.php';

            } catch (\Exception $e) {
                $mensagemErro = "Erro ao processar sua resposta: " . $e->getMessage();
                error_log("Erro ao processar resposta: " . $e->getMessage());
                require_once __DIR__ . '/../Views/responder_pergunta.php';
            }

        } else {
            header("Location: /perguntas_IA/public/pergunta/responder/{$id}");
            exit();
        }
    }
}