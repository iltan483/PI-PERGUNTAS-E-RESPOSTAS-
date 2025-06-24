<?php

namespace App\DAOs;

use App\Core\Database;
use App\Models\RespostaModelo;
use PDO;
use PDOException;

class RespostaModeloDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(RespostaModelo $respostaModelo): ?int
    {
        $sql = "INSERT INTO respostas_modelo (pergunta_id, tipo_modelo, conteudo)
                VALUES (:pergunta_id, :tipo_modelo, :conteudo)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $respostaModelo->getPerguntaId(), PDO::PARAM_INT);
            $stmt->bindValue(':tipo_modelo', $respostaModelo->getTipoModelo());
            $stmt->bindValue(':conteudo', $respostaModelo->getConteudo());
            $stmt->execute();
            $id = (int)$this->pdo->lastInsertId('respostas_modelo_id_seq');
            $respostaModelo->setId($id);
            return $id;
        } catch (PDOException $e) {
            error_log("Erro ao criar resposta modelo: " . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?RespostaModelo
    {
        $sql = "SELECT * FROM respostas_modelo WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();
            if ($data) {
                $respostaModelo = new RespostaModelo($data->pergunta_id, $data->tipo_modelo, $data->conteudo);
                $respostaModelo->setId($data->id)
                               ->setDataCriacao($data->data_criacao);
                return $respostaModelo;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar resposta modelo por ID: " . $e->getMessage());
            return null;
        }
    }

    public function findByPerguntaId(int $perguntaId): ?RespostaModelo
    {
        $sql = "SELECT * FROM respostas_modelo WHERE pergunta_id = :pergunta_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();
            if ($data) {
                $respostaModelo = new RespostaModelo($data->pergunta_id, $data->tipo_modelo, $data->conteudo);
                $respostaModelo->setId($data->id)
                               ->setDataCriacao($data->data_criacao);
                return $respostaModelo;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar resposta modelo por ID da pergunta: " . $e->getMessage());
            return null;
        }
    }

    public function update(RespostaModelo $respostaModelo): bool
    {
        $sql = "UPDATE respostas_modelo SET
                    tipo_modelo = :tipo_modelo,
                    conteudo = :conteudo
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':tipo_modelo', $respostaModelo->getTipoModelo());
            $stmt->bindValue(':conteudo', $respostaModelo->getConteudo());
            $stmt->bindValue(':id', $respostaModelo->getId(), PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar resposta modelo: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM respostas_modelo WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar resposta modelo: " . $e->getMessage());
            return false;
        }
    }
}