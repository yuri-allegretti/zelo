<?php
session_start();
require_once '../zelo/php/conexao.php';

$user_id   = (int) $_SESSION['user_id'];
$ticket_id = (int) ($_GET['ticket_id'] ?? 0);

$stmt = $conn->prepare("SELECT nivel, nome FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$me || !in_array($me['nivel'], ['suporte', 'admin'])) {
    header('Location: ../zelo/login/html/login.html');
    exit;
}

$stmt = $conn->prepare("
    SELECT t.*, c.nome AS nome_usuario 
    FROM tickets t
    JOIN cadastro c ON c.id = t.user_id
    WHERE t.id = ?
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    header('Location: suporte.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Chat #<?= str_pad($ticket_id,3,'0',STR_PAD_LEFT) ?> — Suporte Zelo</title>
    <link rel="stylesheet" href="suporte.css">
    <link rel="stylesheet" href="../zelo/php/style1.css">
</head>
<body>
    <header>
        <a href="suporte.php" style="color:#fff; text-decoration:none;">← Voltar</a>
        <h1>Chat #<?= str_pad($ticket_id,3,'0',STR_PAD_LEFT) ?> — <?= htmlspecialchars($ticket['titulo']) ?></h1>
        <?php if ($ticket['status'] !== 'fechada'): ?>
            <a href="../zelo/php/fechar_ticket.php?ticket_id=<?= $ticket_id ?>" 
               class="sair"
               onclick="return confirm('Fechar este chamado?')">Encerrar</a>
        <?php else: ?>
            <span style="color:#888;">Encerrado</span>
        <?php endif; ?>
    </header>

    <div style="padding:1rem; color:#aaa; font-size:0.85rem;">
        Usuário: <strong><?= htmlspecialchars($ticket['nome_usuario']) ?></strong> |
        Prioridade: <strong><?= $ticket['prioridade'] ?></strong> |
        Status: <strong><?= $ticket['status'] ?></strong>
    </div>

    <div class="chat-box" id="chatBox"></div>

    <?php if ($ticket['status'] !== 'fechada'): ?>
    <div class="chat-input-area">
        <textarea id="inputMsg" class="form-control" rows="2" placeholder="Digite..."></textarea>
        <button class="btn-suporte btn-destaque" onclick="enviarMensagem()">Enviar</button>
    </div>
    <?php else: ?>
        <div class="chat-fechado-aviso">🔒 Chamado encerrado.</div>
    <?php endif; ?>

<script>
const TICKET_ID = <?= $ticket_id ?>;
const USER_ID   = <?= $user_id ?>;
let ultimoId = 0;

function carregarMensagens() {
    fetch(`../zelo/php/api_chat.php?ticket_id=${TICKET_ID}&ultimo_id=${ultimoId}`)
        .then(r => r.json())
        .then(data => {
            const box = document.getElementById('chatBox');
            data.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'chat-msg ' + (msg.user_id == USER_ID ? 'minha' : 'deles');
                div.innerHTML = `
                    <span class="chat-autor">${msg.nome}</span>
                    <span class="chat-texto">${msg.mensagem}</span>
                    <span class="chat-hora">${msg.enviado_em}</span>
                `;
                box.appendChild(div);
                ultimoId = msg.id;
            });
            if (data.length > 0) box.scrollTop = box.scrollHeight;
        });
}

function enviarMensagem() {
    const texto = document.getElementById('inputMsg').value.trim();
    if (!texto) return;
    fetch('../zelo/php/api_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `ticket_id=${TICKET_ID}&mensagem=${encodeURIComponent(texto)}`
    }).then(() => {
        document.getElementById('inputMsg').value = '';
        carregarMensagens();
    });
}

document.getElementById('inputMsg')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensagem(); }
});

carregarMensagens();
setInterval(carregarMensagens, 3000);
</script>
</body>
</html>