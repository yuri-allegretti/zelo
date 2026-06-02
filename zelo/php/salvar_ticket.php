<?php
$user_id = (int) $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=suporte');
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$titulo    = trim($_POST['titulo']    ?? '');//ap
$descricao = trim($_POST['descricao'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$prioridade= trim($_POST['prioridade']?? 'media');

if (!$titulo || !$descricao || !$categoria) {
    header('Location: index.php?page=abrir_ticket&erro=Preencha+todos+os+campos.');
    exit;
}

$stmt = $conn->prepare("INSERT INTO tickets (user_id, titulo, descricao, categoria, prioridade) VALUES (?, ?, ?, ?, ?)");//ap ?
$stmt->bind_param('issss', $user_id, $titulo, $descricao, $categoria, $prioridade);//ap
$stmt->execute();
$ticket_id = $stmt->insert_id;
$stmt->close();

header("Location: index.php?page=chat_usuario&ticket_id=$ticket_id");
exit;