<?php

namespace App\Models;

class RespostaUsuario
{
    private ?int $id = null;
    private int $perguntaId;
    private string $textoResposta;
    private ?string $dataResposta = null;
    private ?float $pontuacao = null;
    private ?string $feedback = null;

    public function __construct(int $perguntaId, string $textoResposta)
    {
        $this->setPerguntaId($perguntaId);
        $this->setTextoResposta($textoResposta);
    }

    // --- Getters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerguntaId(): int
    {
        return $this->perguntaId;
    }

    public function getTextoResposta(): string
    {
        return $this->textoResposta;
    }

    public function getDataResposta(): ?string
    {
        return $this->dataResposta;
    }

    public function getPontuacao(): ?float
    {
        return $this->pontuacao;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    // --- Setters ---
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setPerguntaId(int $perguntaId): self
    {
        if ($perguntaId <= 0) {
            throw new \InvalidArgumentException("ID da pergunta deve ser um número positivo.");
        }
        $this->perguntaId = $perguntaId;
        return $this;
    }

    public function setTextoResposta(string $textoResposta): self
    {
        if (empty(trim($textoResposta))) {
            throw new \InvalidArgumentException("O texto da resposta do usuário não pode ser vazio.");
        }
        $this->textoResposta = $textoResposta;
        return $this;
    }

    public function setDataResposta(string $dataResposta): self
    {
        if (!strtotime($dataResposta)) {
            throw new \InvalidArgumentException("Formato de data de resposta inválido.");
        }
        $this->dataResposta = $dataResposta;
        return $this;
    }

    public function setPontuacao(?float $pontuacao): self
    {
        if ($pontuacao !== null && ($pontuacao < 0 || $pontuacao > 100)) { // Exemplo de range de pontuação
            throw new \InvalidArgumentException("Pontuação deve estar entre 0 e 100.");
        }
        $this->pontuacao = $pontuacao;
        return $this;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;
        return $this;
    }
}