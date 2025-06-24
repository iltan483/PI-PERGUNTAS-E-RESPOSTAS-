<?php

namespace App\Controllers;

use App\Services\PerguntaService;

class DashboardController
{
    private PerguntaService $perguntaService;

    public function __construct()
    {
        $this->perguntaService = new PerguntaService();
    }

    public function index()
    {
        $totalPerguntas = count($this->perguntaService->buscarTodasPerguntas()); 
        
        require_once __DIR__ . '/../Views/dashboard.php';
    }

}