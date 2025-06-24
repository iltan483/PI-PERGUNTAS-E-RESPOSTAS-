<?php
// app/Models/RespostaModelo.php

namespace App\Models;

class RespostaModelo
{
    private ?int $id = null;
    private int $perguntaId;
    private string $tipoModelo; // 'exata', 'criterios', 'palavras_chave'
    private string $conteudo;
    private ?string $dataCriacao = null; // Representa um TIMESTAMP do DB

    public function __construct(int $perguntaId, string $tipoModelo, string $conteudo)
    {
        $this->setPerguntaId($perguntaId);
        $this->setTipoModelo($tipoModelo);
        $this->setConteudo($conteudo);
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

    public function getTipoModelo(): string
    {
        return $this->tipoModelo;
    }

    public function getConteudo(): string
    {
        return $this->conteudo;
    }

    public function getDataCriacao(): ?string
    {
        return $this->dataCriacao;
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

    public function setTipoModelo(string $tipoModelo): self
    {
        $tiposValidos = ['exata', 'criterios', 'palavras_chave'];
        if (!in_array($tipoModelo, $tiposValidos)) {
            throw new \InvalidArgumentException("Tipo de modelo inválido: {$tipoModelo}.");
        }
        $this->tipoModelo = $tipoModelo;
        return $this;
    }

    public function setConteudo(string $conteudo): self
    {
        if (empty(trim($conteudo))) {
            throw new \InvalidArgumentException("O conteúdo do modelo de resposta não pode ser vazio.");
        }
        $this->conteudo = $conteudo;
        return $this;
    }

    public function setDataCriacao(string $dataCriacao): self
    {
        if (!strtotime($dataCriacao)) {
            throw new \InvalidArgumentException("Formato de data de criação inválido.");
        }
        $this->dataCriacao = $dataCriacao;
        return $this;
    }
}