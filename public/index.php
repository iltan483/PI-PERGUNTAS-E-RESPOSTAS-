<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignora linhas que são comentários ou não contêm '='
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove aspas simples ou duplas do valor, se existirem
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = trim($value, '"');
        } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = trim($value, "'");
        }

        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

// Configurações de exibição de erros para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Controllers\PerguntaController;
use App\Controllers\DashboardController;
use App\Controllers\RespostaController; // Adicionado para o /respostas
use App\Core\Database;

// Inicializa a conexão com o banco de dados
Database::getInstance(); // Conecta-se ao banco de dados no início da aplicação

// Lógica de roteamento
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$scriptName = trim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Remove o nome do script (ex: /perguntas_IA/public) da URI
if (strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}
$requestUri = trim($requestUri, '/');

$uriSegments = explode('/', $requestUri);

// Define o controller e o método baseados na URI
$controllerName = !empty($uriSegments[0]) ? ucfirst($uriSegments[0]) . 'Controller' : 'DashboardController';
$methodName = !empty($uriSegments[1]) ? $uriSegments[1] : 'index';

// Remove os segmentos de controller e método para obter os parâmetros
array_shift($uriSegments);
if (!empty($uriSegments[0])) { // Verifica se ainda há um segmento de método para remover
    array_shift($uriSegments);
}
$params = $uriSegments; // Os segmentos restantes são os parâmetros

try {
    $controllerClass = "App\\Controllers\\{$controllerName}";

    if (!class_exists($controllerClass)) {
        // Redireciona para DashboardController se a URI estiver vazia ou não levar a um controller válido
        if (empty($uriSegments[0]) && $controllerName === 'DashboardController' && $methodName === 'index') {
             // Você pode adicionar um tratamento específico aqui se o DashboardController não existir,
             // mas geralmente ele seria a página inicial padrão.
        }
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>A página que você está procurando não foi encontrada.</p>";
        exit();
    }

    $controller = new $controllerClass();

    if (!method_exists($controller, $methodName)) {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>O método '$methodName' não existe no Controller '$controllerName'.</p>";
        exit();
    }

    // Chama o método do controller com os parâmetros
    call_user_func_array([$controller, $methodName], $params);

} catch (PDOException $e) {
    // Erro de banco de dados
    error_log("Erro de Banco de Dados: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
    echo "<h1>Erro Interno do Servidor</h1><p>Ocorreu um problema ao acessar o banco de dados. Tente novamente mais tarde.</p>";
} catch (Exception $e) {
    // Outros erros da aplicação
    error_log("Erro no roteador/controller: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
    echo "<h1>Erro Interno do Servidor</h1><p>Ocorreu um problema inesperado: " . htmlspecialchars($e->getMessage()) . "</p>";
}