<?php

ob_start();
?>
<h2>Detalhes da Pergunta</h2>

<?php if (!isset($pergunta)): ?>
    <div class="message error">Pergunta não encontrada.</div>
<?php else: ?>
    <h3><?php echo htmlspecialchars($pergunta->getTextoPergunta()); ?></h3>
    <p><strong>ID:</strong> <?php echo htmlspecialchars($pergunta->getId()); ?></p>
    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($pergunta->getTipoPergunta()); ?></p>
    <p><strong>Tema:</strong> <?php echo htmlspecialchars($pergunta->getTema() ?? 'N/A'); ?></p>

    <?php if ($pergunta->getTipoPergunta() === 'objetiva' && !empty($opcoes)): ?>
        <h4>Opções de Resposta:</h4>
        <ul>
            <?php foreach ($opcoes as $opcao): ?>
                <li>
                    <?php echo htmlspecialchars($opcao->getTextoOpcao()); ?>
                    (<?php echo $opcao->isCorreta() ? 'Correta' : 'Incorreta'; ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (isset($respostaModelo) && $respostaModelo): ?>
        <h4>Modelo de Resposta:</h4>
        <p><strong>Tipo do Modelo:</strong> <?php echo htmlspecialchars($respostaModelo->getTipoModelo()); ?></p>
        <p><strong>Conteúdo do Modelo:</strong> <?php echo nl2br(htmlspecialchars($respostaModelo->getConteudo())); ?></p>
    <?php endif; ?>

    <p><a href="/perguntas_IA/public/pergunta/listar">Voltar para a lista de perguntas</a></p>

<?php endif; ?>

<?php
$content = ob_get_clean();


require_once __DIR__ . '/layout.php';
?>