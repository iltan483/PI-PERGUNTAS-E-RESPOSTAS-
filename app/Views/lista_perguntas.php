<?php

ob_start();
?>
<h2>Todas as Perguntas Cadastradas</h2>

<?php if (empty($perguntas)): ?>
    <p>Nenhuma pergunta encontrada. <a href="/perguntas_IA/public/pergunta/gerarForm">Gere algumas agora!</a></p>
<?php else: ?>
    <ul class="list-group">
        <?php foreach ($perguntas as $pergunta): ?>
            <li>
                <span><?php echo htmlspecialchars($pergunta->getTextoPergunta()); ?> (Tipo: <?php echo htmlspecialchars($pergunta->getTipoPergunta()); ?>)</span>
                <a href="/perguntas_IA/public/pergunta/detalhes/<?php echo htmlspecialchars($pergunta->getId()); ?>">Ver Detalhes</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$content = ob_get_clean();


require_once __DIR__ . '/layout.php';
?>