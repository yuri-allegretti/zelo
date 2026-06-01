<?php
$user_id   = (int) $_SESSION['user_id'];
$ticket_id = (int) ($_GET['ticket_id'] ?? 0);

if (!$ticket_id) {
    header('Location: index.php?page=suporte');
    exit;
}


$stmt = $conn->prepare("
    SELECT t.*, c.nome AS nome_agente 
    FROM tickets t
    LEFT JOIN cadastro c ON c.id = t.agente_id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo "<div class='page-container'><p>Chamado não encontrado.</p></div>";
    return;
}
?>

<div class="page-container">
    <a href="?page=suporte" class="btn-voltar">← Voltar</a>
    
    <div class="chat-header">
        <div>
            <h2><?= htmlspecialchars($ticket['titulo']) ?></h2>
            <span class="badge-status <?= $ticket['status'] ?>"><?= $ticket['status'] ?></span>
            <span class="badge-prioridade <?= $ticket['prioridade'] ?>"><?= $ticket['prioridade'] ?></span>
        </div>
        <?php if ($ticket['nome_agente']): ?>
            <div class="chat-agente-info">👤 Agente: <strong><?= htmlspecialchars($ticket['nome_agente']) ?></strong></div>
        <?php else: ?>
            <div class="chat-agente-info">⏳ Aguardando agente...</div>
        <?php endif; ?>
    </div>

    <div class="chat-box" id="chatBox">
        
    </div>

    <?php if ($ticket['status'] !== 'fechada'): ?>
    <div class="chat-input-area">
        <textarea id="inputMsg" class="form-control" rows="2" 
                  placeholder="Digite sua mensagem..."></textarea>
        <button class="btn-suporte btn-destaque" onclick="enviarMensagem()">Enviar</button>
    </div>
    <?php else: ?>
        <div class="chat-fechado-aviso">🔒 Este chamado foi encerrado.</div>
    <?php endif; ?>
</div>

<script>
const TICKET_ID  = <?= $ticket_id ?>;
const USER_ID    = <?= $user_id ?>;
let ultimoId = 0;

function carregarMensagens() {
    fetch(`/zelo/zelo/php/api_chat.php?ticket_id=${TICKET_ID}&ultimo_id=${ultimoId}`)
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

   fetch('/zelo/zelo/php/api_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `ticket_id=${TICKET_ID}&mensagem=${encodeURIComponent(texto)}`
    }).then(() => {
        document.getElementById('inputMsg').value = '';
        carregarMensagens();
    });
}

document.getElementById('inputMsg')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviarMensagem();
    }
});

carregarMensagens();
setInterval(carregarMensagens, 3000); // polling a cada 3s
</script>