<?php

namespace App\DAOs;

use App\Core\Database;
use App\Models\OpcaoResposta;
use PDO;
use PDOException;

class OpcaoRespostaDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(OpcaoResposta $opcao): ?int
    {
        $sql = "INSERT INTO opcoes_resposta (pergunta_id, texto_opcao, e_correta)
                VALUES (:pergunta_id, :texto_opcao, :e_correta)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $opcao->getPerguntaId(), PDO::PARAM_INT);
            $stmt->bindValue(':texto_opcao', $opcao->getTextoOpcao());
            $stmt->bindValue(':e_correta', $opcao->isCorreta(), PDO::PARAM_BOOL);
            $stmt->execute();
            $id = (int)$this->pdo->lastInsertId('opcoes_resposta_id_seq');
            $opcao->setId($id);
            return $id;
        } catch (PDOException $e) {
            error_log("Erro ao criar opção de resposta: " . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?OpcaoResposta
    {
        $sql = "SELECT * FROM opcoes_resposta WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();
            if ($data) {
                $opcao = new OpcaoResposta($data->pergunta_id, $data->texto_opcao, $data->e_correta);
                $opcao->setId($data->id);
                return $opcao;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar opção de resposta por ID: " . $e->getMessage());
            return null;
        }
    }

    public function findByPerguntaId(int $perguntaId): array
    {
        $sql = "SELECT * FROM opcoes_resposta WHERE pergunta_id = :pergunta_id";
        $opcoes = [];
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmt->execute();
            while ($data = $stmt->fetch()) {
                $opcao = new OpcaoResposta($data->pergunta_id, $data->texto_opcao, $data->e_correta);
                $opcao->setId($data->id);
                $opcoes[] = $opcao;
            }
            return $opcoes;
        } catch (PDOException $e) {
            error_log("Erro ao buscar opções de resposta por ID da pergunta: " . $e->getMessage());
            return [];
        }
    }

    public function update(OpcaoResposta $opcao): bool
    {
        $sql = "UPDATE opcoes_resposta SET
                    texto_opcao = :texto_opcao,
                    e_correta = :e_correta
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':texto_opcao', $opcao->getTextoOpcao());
            $stmt->bindValue(':e_correta', $opcao->isCorreta(), PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $opcao->getId(), PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar opção de resposta: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM opcoes_resposta WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar opção de resposta: " . $e->getMessage());
            return false;
        }
    }
}