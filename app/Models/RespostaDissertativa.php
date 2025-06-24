<?php

namespace App\Models;

use App\Interfaces\IAvaliavel;

class RespostaDissertativa extends RespostaUsuario implements IAvaliavel
{

    public function avaliar(string $respostaUsuario, string $respostaModelo): float
    {
        $pontuacao = 0.0;
        $palavrasChaveModelo = array_map('trim', explode(',', strtolower($respostaModelo)));
        $totalPalavrasChave = count($palavrasChaveModelo);

        if ($totalPalavrasChave === 0) {
            return 0.0; 
        }

        $respostaUsuarioLower = strtolower($respostaUsuario);

        foreach ($palavrasChaveModelo as $palavraChave) {
            if (str_contains($respostaUsuarioLower, $palavraChave)) {
                $pontuacao += (100.0 / $totalPalavrasChave);
            }
        }

        return min(100.0, $pontuacao);
    }
}