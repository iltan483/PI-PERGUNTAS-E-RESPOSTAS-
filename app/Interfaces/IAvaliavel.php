<?php

namespace App\Interfaces;

interface IAvaliavel
{

    public function avaliar(string $respostaUsuario, string $respostaModelo): float;
}