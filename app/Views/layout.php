<?php

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Perguntas com IA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 1em 0;
            text-align: center;
        }
        .nav {
            background-color: #333;
            overflow: hidden;
        }
        .nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            padding: 20px;
            margin: 0 auto;
            max-width: 960px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            color: #777;
            font-size: 0.9em;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        ul.list-group {
            list-style: none;
            padding: 0;
        }
        ul.list-group li {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        ul.list-group li a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        ul.list-group li a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gerador de Perguntas com IA</h1>
    </div>
    <div class="nav">
        <a href="/perguntas_IA/public/">Dashboard</a>
        <a href="/perguntas_IA/public/pergunta/gerarForm">Gerar Perguntas</a>
        <a href="/perguntas_IA/public/pergunta/listar">Listar Perguntas</a>
    </div>

    <div class="container">
        <?php
        if (isset($content)) {
            echo $content;
        }
        ?>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Gerador de Perguntas com IA. Todos os direitos reservados.</p>
    </div>
</body>
</html>