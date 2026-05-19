<?php
$user_id = (int) $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT status FROM solicitacao_suporte WHERE user_id = ? ORDER BY data_solicitacao DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$solicitacao = $result->fetch_assoc();
$stmt->close();

if ($solicitacao) {
    if ($solicitacao['status'] === 'pendente') { ?>
        <div class="page-container">
            <div class="status-box pendente">
                <span class="status-icon">⏳</span>
                <h2>Solicitação em análise</h2>
                <p>Sua candidatura está sendo avaliada. Te avisaremos quando houver resposta.</p>
                <a href="?page=suporte" class="btn-suporte">Voltar</a>
            </div>
        </div>
    <?php return; }

    if ($solicitacao['status'] === 'aprovada') { ?>
        <script>
            window.location.href = '../../Suporte/suporte.html'
        </script>
    <?php return; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    
</body>
</html>

<div class="page-container">
    <a href="?page=suporte" class="btn-voltar">← Voltar</a>
    <h1 class="titulo-pagina">Candidatura — Agente de Suporte</h1>
    <p class="subtitulo-pagina">Preencha os campos abaixo. Sua solicitação será avaliada por um administrador.</p>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alerta-erro"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alerta-sucesso">Candidatura enviada com sucesso! Aguarde a avaliação.</div>
    <?php endif; ?>

    <form action="?page=salvar_suporte" method="POST" class="form-suporte">
        <div class="form-group">
            <label for="nome_completo">Nome completo *</label>
            <input type="text" id="nome_completo" name="nome_completo" class="form-control" placeholder="Seu nome completo" required>
        </div>

        <div class="form-group">
            <label for="idade">Idade *</label>
            <input type="number" id="idade" name="idade" class="form-control" placeholder="Ex: 22" min="16" max="100" required>
        </div>

        <div class="form-group">
            <label for="motivacao">Por que você quer ser agente de suporte? *</label>
            <textarea id="motivacao" name="motivacao" class="form-control" rows="5" placeholder="Conte sua motivação..." required></textarea>
        </div>

        <div class="form-group">
            <label for="experiencia">Experiência prévia <span class="opcional">(opcional)</span></label>
            <textarea id="experiencia" name="experiencia" class="form-control" rows="4" placeholder="Descreva qualquer experiência relevante..."></textarea>
        </div>

        <button type="submit" class="btn-suporte btn-destaque btn-submit">Enviar candidatura</button>
    </form>
</div>