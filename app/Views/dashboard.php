<?php

ob_start();
?>
<h2>Dashboard</h2>
<p>Bem-vindo ao Gerador de Perguntas com IA!</p>
<p>Aqui você pode gerar perguntas automaticamente a partir de um contexto e gerenciar suas perguntas.</p>

<?php if (isset($totalPerguntas)): ?>
    <p>Total de perguntas cadastradas: <strong><?php echo htmlspecialchars($totalPerguntas); ?></strong></p>
<?php endif; ?>

<p>Use o menu de navegação acima para:</p>
<ul>
    <li><strong>Gerar Perguntas:</strong> Crie novas perguntas objetivas e dissertativas com o auxílio da inteligência artificial.</li>
    <li><strong>Listar Perguntas:</strong> Visualize todas as perguntas já geradas e salvas.</li>
</ul>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/layout.php';
?>