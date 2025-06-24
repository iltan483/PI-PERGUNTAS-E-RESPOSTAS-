<?php
// app/Models/OpcaoResposta.php

namespace App\Models;

class OpcaoResposta
{
    private ?int $id = null;
    private int $perguntaId;
    private string $textoOpcao;
    private bool $eCorreta;

    public function __construct(int $perguntaId, string $textoOpcao, bool $eCorreta)
    {
        $this->setPerguntaId($perguntaId);
        $this->setTextoOpcao($textoOpcao);
        $this->eCorreta = $eCorreta;
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

    public function getTextoOpcao(): string
    {
        return $this->textoOpcao;
    }

    public function isCorreta(): bool
    {
        return $this->eCorreta;
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

    public function setTextoOpcao(string $textoOpcao): self
    {
        if (empty(trim($textoOpcao))) {
            throw new \InvalidArgumentException("O texto da opção não pode ser vazio.");
        }
        $this->textoOpcao = $textoOpcao;
        return $this;
    }

    public function setCorreta(bool $eCorreta): self
    {
        $this->eCorreta = $eCorreta;
        return $this;
    }
}