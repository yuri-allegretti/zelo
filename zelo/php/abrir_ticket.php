<?php
$user_id = (int) $_SESSION['user_id'];


$stmt = $conn->prepare("
    SELECT id FROM tickets 
    WHERE user_id = ? AND status IN ('aberta','em_andamento') 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existente) {
    header("Location: index.php?page=chat_usuario&ticket_id=" . $existente['id']);
    exit;
}
?>

<div class="page-container">
    <a href="?page=suporte" class="btn-voltar">← Voltar</a>
    <h1 class="titulo-pagina">Abrir chamado de suporte</h1>
    <p class="subtitulo-pagina">Descreva seu problema e um agente irá te atender.</p>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alerta-erro"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="?page=salvar_ticket" method="POST" class="form-suporte">
        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" class="form-control" 
                   placeholder="Ex: Saldo incorreto" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoria *</label>
            <select id="categoria" name="categoria" class="form-control" required>
                <option value="">Selecione...</option>
                <option value="saldo">Saldo</option>
                <option value="transacao">Transação</option>
                <option value="conexao_banco">Conexão com banco</option>
                <option value="conta">Conta</option>
                <option value="outro">Outro</option>
            </select>
        </div>

        <div class="form-group">
            <label for="prioridade">Prioridade *</label>
            <select id="prioridade" name="prioridade" class="form-control" required>
                <option value="baixa">Baixa</option>
                <option value="media" selected>Média</option>
                <option value="alta">Alta</option>
            </select>
        </div>

        <div class="form-group">
            <label for="descricao">Descreva o problema *</label>
            <textarea id="descricao" name="descricao" class="form-control" 
                      rows="5" placeholder="Explique com detalhes..." required></textarea>
        </div>

        <button type="submit" class="btn-suporte btn-destaque btn-submit">Abrir chamado</button>
    </form>
</div>