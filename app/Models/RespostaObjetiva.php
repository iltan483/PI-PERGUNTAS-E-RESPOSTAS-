<?php

namespace App\Models;

use App\Interfaces\IAvaliavel;

class RespostaObjetiva extends RespostaUsuario implements IAvaliavel
{

    public function avaliar(string $respostaUsuario, string $respostaModelo): float
    {
        if (strtolower(trim($respostaUsuario)) === strtolower(trim($respostaModelo))) {
            return 100.0;
        }
        return 0.0;
    }
}