<?php

namespace App\DAOs;

use App\Core\Database;
use App\Models\RespostaUsuario;
use PDO;
use PDOException;

class RespostaUsuarioDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(RespostaUsuario $respostaUsuario): ?int
    {
        $sql = "INSERT INTO respostas_usuario (pergunta_id, texto_resposta, pontuacao, feedback)
                VALUES (:pergunta_id, :texto_resposta, :pontuacao, :feedback)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $respostaUsuario->getPerguntaId(), PDO::PARAM_INT);
            $stmt->bindValue(':texto_resposta', $respostaUsuario->getTextoResposta());
            $stmt->bindValue(':pontuacao', $respostaUsuario->getPontuacao());
            $stmt->bindValue(':feedback', $respostaUsuario->getFeedback());
            $stmt->execute();
            $id = (int)$this->pdo->lastInsertId('respostas_usuario_id_seq');
            $respostaUsuario->setId($id);
            return $id;
        } catch (PDOException $e) {
            error_log("Erro ao criar resposta do usuário: " . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?RespostaUsuario
    {
        $sql = "SELECT * FROM respostas_usuario WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();
            if ($data) {
                $respostaUsuario = new RespostaUsuario($data->pergunta_id, $data->texto_resposta);
                $respostaUsuario->setId($data->id)
                               ->setDataResposta($data->data_resposta)
                               ->setPontuacao($data->pontuacao)
                               ->setFeedback($data->feedback);
                return $respostaUsuario;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar resposta do usuário por ID: " . $e->getMessage());
            return null;
        }
    }

    public function findByPerguntaId(int $perguntaId): array
    {
        $sql = "SELECT * FROM respostas_usuario WHERE pergunta_id = :pergunta_id ORDER BY data_resposta DESC";
        $respostas = [];
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmt->execute();
            while ($data = $stmt->fetch()) {
                $respostaUsuario = new RespostaUsuario($data->pergunta_id, $data->texto_resposta);
                $respostaUsuario->setId($data->id)
                               ->setDataResposta($data->data_resposta)
                               ->setPontuacao($data->pontuacao)
                               ->setFeedback($data->feedback);
                $respostas[] = $respostaUsuario;
            }
            return $respostas;
        } catch (PDOException $e) {
            error_log("Erro ao buscar respostas do usuário por ID da pergunta: " . $e->getMessage());
            return [];
        }
    }

    public function update(RespostaUsuario $respostaUsuario): bool
    {
        $sql = "UPDATE respostas_usuario SET
                    texto_resposta = :texto_resposta,
                    pontuacao = :pontuacao,
                    feedback = :feedback
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':texto_resposta', $respostaUsuario->getTextoResposta());
            $stmt->bindValue(':pontuacao', $respostaUsuario->getPontuacao());
            $stmt->bindValue(':feedback', $respostaUsuario->getFeedback());
            $stmt->bindValue(':id', $respostaUsuario->getId(), PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar resposta do usuário: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM respostas_usuario WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar resposta do usuário: " . $e->getMessage());
            return false;
        }
    }
}