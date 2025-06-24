<?php

namespace App\Services;

use App\DAOs\PerguntaDAO;
use App\DAOs\OpcaoRespostaDAO;
use App\DAOs\RespostaModeloDAO;
use App\DAOs\RespostaUsuarioDAO; 
use App\Factories\PerguntaFactory;
use App\Models\Pergunta;
use App\Models\OpcaoResposta;
use App\Models\RespostaModelo;
use App\Models\RespostaUsuario; 

class PerguntaService
{
    private PerguntaDAO $perguntaDAO;
    private OpcaoRespostaDAO $opcaoRespostaDAO;
    private RespostaModeloDAO $respostaModeloDAO;
    private RespostaUsuarioDAO $respostaUsuarioDAO; 

    public function __construct()
    {
        $this->perguntaDAO = new PerguntaDAO();
        $this->opcaoRespostaDAO = new OpcaoRespostaDAO();
        $this->respostaModeloDAO = new RespostaModeloDAO();
        $this->respostaUsuarioDAO = new RespostaUsuarioDAO(); 
    }

    public function criarNovaPerguntaComDetalhes(
        string $tipoPergunta,
        string $textoPergunta,
        ?string $tema,
        array $opcoesRespostas,
        string $conteudoRespostaModelo,
        string $tipoRespostaModelo
    ): ?Pergunta {
        try {
            $pergunta = PerguntaFactory::criarPergunta(
                $tipoPergunta,
                $textoPergunta,
                $tema,
                $opcoesRespostas, 
                $conteudoRespostaModelo,
                $tipoRespostaModelo
            );

            $perguntaId = $this->perguntaDAO->create($pergunta);
            if (!$perguntaId) {
                return null; 
            }
            $pergunta->setId($perguntaId); 

            $respostaModelo = new RespostaModelo($perguntaId, $tipoRespostaModelo, $conteudoRespostaModelo);
            $respostaModeloId = $this->respostaModeloDAO->create($respostaModelo);
            if (!$respostaModeloId) {
                return null;
            }

            if ($tipoPergunta === 'objetiva' && !empty($opcoesRespostas)) {
                foreach ($opcoesRespostas as $opcaoData) {
                    $opcao = new OpcaoResposta($perguntaId, $opcaoData['texto'], $opcaoData['correta']);
                    if (!$this->opcaoRespostaDAO->create($opcao)) {
                        return null;
                    }
                }
            }

            return $pergunta;

        } catch (\InvalidArgumentException $e) {
            error_log("Erro de validação ao criar pergunta: " . $e->getMessage());
            return null;
        } catch (\PDOException $e) {
            error_log("Erro de banco de dados ao criar pergunta: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log("Erro inesperado ao criar pergunta: " . $e->getMessage());
            return null;
        }
    }

    public function buscarTodasPerguntas(): array
    {
        return $this->perguntaDAO->findAll();
    }


    public function buscarPerguntaCompleta(int $perguntaId): ?array
    {
        $pergunta = $this->perguntaDAO->findById($perguntaId);
        if (!$pergunta) {
            return null;
        }

        $dados = [
            'pergunta' => $pergunta,
            'opcoes' => [],
            'resposta_modelo' => null
        ];

        if ($pergunta->getTipoPergunta() === 'objetiva') {
            $dados['opcoes'] = $this->opcaoRespostaDAO->findByPerguntaId($pergunta->getId());
        }

        $dados['resposta_modelo'] = $this->respostaModeloDAO->findByPerguntaId($pergunta->getId());

        return $dados;
    }

    public function salvarRespostaUsuario(RespostaUsuario $resposta): ?int
    {
        try {
            return $this->respostaUsuarioDAO->create($resposta);
        } catch (\PDOException $e) {
            error_log("Erro de banco de dados ao salvar resposta do usuário: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log("Erro inesperado ao salvar resposta do usuário: " . $e->getMessage());
            return null;
        }
    }

    public function buscarRespostasDeUsuarioParaPergunta(int $perguntaId): array
    {
        return $this->respostaUsuarioDAO->findByPerguntaId($perguntaId);
    }
}