<?php
$user_id = (int) $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=suporte');
    exit;
}

$user_id       = (int) $_SESSION['user_id'];
$nome_completo = trim($_POST['nome_completo'] ?? '');
$idade         = (int) ($_POST['idade'] ?? 0);//ap
$motivacao     = trim($_POST['motivacao'] ?? '');
$experiencia   = trim($_POST['experiencia'] ?? '') ?: null;


if (!$nome_completo || !$motivacao || $idade < 16 || $idade > 100) {//ap
    header('Location: index.php?page=solicitar_suporte&erro=Preencha+todos+os+campos+corretamente.');
    exit;
}

// Bloquear se já tem pendente ou aprovada
$stmt = $conn->prepare("SELECT id FROM solicitacao_suporte WHERE user_id = ? AND status IN ('pendente', 'aprovada') LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Location: index.php?page=solicitar_suporte');
    exit;
}
$stmt->close();

// Salvar
$stmt = $conn->prepare("INSERT INTO solicitacao_suporte (user_id, nome_completo, idade, motivacao, experiencia) VALUES (?, ?, ?, ?, ?)");//ap ?
$stmt->bind_param("issss", $user_id, $nome_completo, $idade, $motivacao, $experiencia);//ap is

if ($stmt->execute()) { 
    header('Location: index.php?page=solicitar_suporte&sucesso=1');
} else {
    header('Location: index.php?page=solicitar_suporte&erro=Erro+ao+enviar.+Tente+novamente.');
}
$stmt->close();
exit;