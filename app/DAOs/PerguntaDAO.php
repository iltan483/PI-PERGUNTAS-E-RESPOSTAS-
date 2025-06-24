<?php

namespace App\DAOs;

use App\Core\Database;
use App\Models\Pergunta;
use PDO;
use PDOException;

class PerguntaDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(Pergunta $pergunta): ?int
    {
        $sql = "INSERT INTO perguntas (texto_pergunta, tipo_pergunta, tema, status_pergunta)
                VALUES (:texto_pergunta, :tipo_pergunta, :tema, :status_pergunta)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':texto_pergunta', $pergunta->getTextoPergunta());
            $stmt->bindValue(':tipo_pergunta', $pergunta->getTipoPergunta());
            $stmt->bindValue(':tema', $pergunta->getTema());
            //$stmt->bindValue(':fonte_texto', $pergunta->getFonteTexto());
            $stmt->bindValue(':status_pergunta', $pergunta->getStatusPergunta());

            $stmt->execute();

            $id = (int)$this->pdo->lastInsertId('perguntas_id_seq');
            $pergunta->setId($id);
            return $id;
        } catch (PDOException $e) {
            error_log("Erro ao criar pergunta: " . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?Pergunta
    {
        $sql = "SELECT * FROM perguntas WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_OBJ); // ← Aqui é a correção importante
            $data = $stmt->fetch();

            if ($data) {
                $pergunta = new Pergunta($data->texto_pergunta, $data->tipo_pergunta, $data->tema, $data->fonte_texto);
                $pergunta->setId($data->id)
                         ->setDataCriacao($data->data_criacao)
                         ->setRespostaModeloId($data->resposta_modelo_id)
                         ->setStatusPergunta($data->status_pergunta);
                return $pergunta;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar pergunta por ID: " . $e->getMessage());
            return null;
        }
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM perguntas ORDER BY data_criacao DESC";
        $perguntas = [];
        try {
            $stmt = $this->pdo->query($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ); // ← Correção aqui também

            while ($data = $stmt->fetch()) {
                $pergunta = new Pergunta($data->texto_pergunta, $data->tipo_pergunta, $data->tema, $data->fonte_texto);
                $pergunta->setId($data->id)
                         ->setDataCriacao($data->data_criacao)
                         ->setRespostaModeloId($data->resposta_modelo_id)
                         ->setStatusPergunta($data->status_pergunta);
                $perguntas[] = $pergunta;
            }
            return $perguntas;
        } catch (PDOException $e) {
            error_log("Erro ao buscar todas as perguntas: " . $e->getMessage());
            return [];
        }
    }

    public function update(Pergunta $pergunta): bool
    {
        $sql = "UPDATE perguntas SET
                    texto_pergunta = :texto_pergunta,
                    tipo_pergunta = :tipo_pergunta,
                    tema = :tema,
                    fonte_texto = :fonte_texto,
                    resposta_modelo_id = :resposta_modelo_id,
                    status_pergunta = :status_pergunta
                WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':texto_pergunta', $pergunta->getTextoPergunta());
            $stmt->bindValue(':tipo_pergunta', $pergunta->getTipoPergunta());
            $stmt->bindValue(':tema', $pergunta->getTema());
            $stmt->bindValue(':fonte_texto', $pergunta->getFonteTexto());
            $stmt->bindValue(':resposta_modelo_id', $pergunta->getRespostaModeloId(), PDO::PARAM_INT);
            $stmt->bindValue(':status_pergunta', $pergunta->getStatusPergunta());
            $stmt->bindValue(':id', $pergunta->getId(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar pergunta: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM perguntas WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar pergunta: " . $e->getMessage());
            return false;
        }
    }
}
