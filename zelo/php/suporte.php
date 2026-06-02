<?php
$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nivel FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="page-container">
    <h1 class="titulo-pagina">Ajuda & Suporte</h1>
    <p class="subtitulo-pagina">Precisa de ajuda? Fale com nosso time ou torne-se um agente.</p>

    <div class="suporte-cards">
        <div class="suporte-card">
            <div class="suporte-card-icon">💬</div>
            <h3>Falar com suporte</h3>
            <p>Entre em contato com um agente disponível para tirar suas dúvidas.</p>
            <a href="?page=abrir_ticket" class="btn-suporte">Abrir chat</a>
        </div>

        <div class="suporte-card destaque">
            <div class="suporte-card-icon">🛡️</div>
            <h3>Seja um agente de suporte</h3>
            <p>Quer ajudar outros usuários? Candidate-se para fazer parte do nosso time.</p>
            <a href="?page=solicitar_suporte" class="btn-suporte btn-destaque">Quero ser agente</a>           
        </div>
        <?php if ($me['nivel'] === 'admin'): ?>
    <div class="suporte-card" style="border-color:#f5c518;">
        <div class="suporte-card-icon">⚙️</div>
        <h3>Painel Admin</h3>
        <p>Gerencie as solicitações de agentes de suporte.</p>
        <a href="?page=admin_suporte" class="btn-suporte" style="border-color:#f5c518; color:#f5c518;">Acessar painel</a>
    </div>
    <?php endif; ?>
    </div>
</div>