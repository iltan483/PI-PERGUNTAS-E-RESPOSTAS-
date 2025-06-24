<?php

namespace App\Models;

class Pergunta
{
    private ?int $id;
    private string $textoPergunta;
    private string $tipoPergunta;
    private ?string $tema;
    private ?int $respostaModeloId;
    private ?string $dataCriacao;
    private ?string $statusPergunta;

    public array $opcoesTemporarias = [];
    public ?string $conteudoModeloTemporario = null;
    public ?string $tipoModeloTemporario = null;

    public function __construct(
        string $textoPergunta,
        string $tipoPergunta,
        ?string $tema = null,
        ?int $id = null,
        ?int $respostaModeloId = null,
        ?string $dataCriacao = null,
        ?string $statusPergunta = null
    ) {
        $this->id = $id;
        $this->textoPergunta = $textoPergunta;
        $this->tipoPergunta = $tipoPergunta;
        $this->tema = $tema;
        $this->respostaModeloId = $respostaModeloId;
        $this->dataCriacao = $dataCriacao;
        $this->statusPergunta = $statusPergunta;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTextoPergunta(): string
    {
        return $this->textoPergunta;
    }
    public function getTipoPergunta(): string
    {
        return $this->tipoPergunta;
    }
    public function getTema(): ?string
    {
        return $this->tema;
    }
    public function getRespostaModeloId(): ?int
    {
        return $this->respostaModeloId;
    }
    public function getDataCriacao(): ?string
    {
        return $this->dataCriacao;
    }
    public function getStatusPergunta(): ?string
    {
        return $this->statusPergunta;
    }

    // Setters com return $this
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
    public function setTextoPergunta(string $textoPergunta): self
    {
        $this->textoPergunta = $textoPergunta;
        return $this;
    }
    public function setTipoPergunta(string $tipoPergunta): self
    {
        $this->tipoPergunta = $tipoPergunta;
        return $this;
    }
    public function setTema(?string $tema): self
    {
        $this->tema = $tema;
        return $this;
    }
    public function setRespostaModeloId(?int $respostaModeloId): self
    {
        $this->respostaModeloId = $respostaModeloId;
        return $this;
    }
    public function setDataCriacao(?string $dataCriacao): self
    {
        $this->dataCriacao = $dataCriacao;
        return $this;
    }
    public function setStatusPergunta(?string $statusPergunta): self
    {
        $this->statusPergunta = $statusPergunta;
        return $this;
    }
}
