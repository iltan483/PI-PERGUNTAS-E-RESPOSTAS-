<?php

ob_start();
?>
<h2>Gerar Novas Perguntas</h2>

<?php if (isset($mensagemSucesso)): ?>
    <div class="message success"><?php echo htmlspecialchars($mensagemSucesso); ?></div>
<?php endif; ?>

<?php if (isset($mensagemErro)): ?>
    <div class="message error"><?php echo htmlspecialchars($mensagemErro); ?></div>
<?php endif; ?>

<form action="/perguntas_IA/public/pergunta/gerar" method="POST">
    <div class="form-group">
        <label for="contexto">Contexto para Geração de Perguntas:</label>
        <textarea id="contexto" name="contexto" required placeholder="Ex: 'O conceito de polimorfismo em programação orientada a objetos e suas aplicações.'"></textarea>
        <small>Forneça um texto ou tema detalhado para a IA gerar perguntas relevantes.</small>
    </div>

    <div class="form-group">
        <label for="num_objetivas">Número de Perguntas Objetivas:</label>
        <input type="number" id="num_objetivas" name="num_objetivas" value="1" min="0" max="5" required>
    </div>

    <div class="form-group">
        <label for="num_dissertativas">Número de Perguntas Dissertativas:</label>
        <input type="number" id="num_dissertativas" name="num_dissertativas" value="1" min="0" max="5" required>
    </div>

    <button type="submit" class="btn">Gerar e Salvar Perguntas</button>
</form>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/layout.php';
?>