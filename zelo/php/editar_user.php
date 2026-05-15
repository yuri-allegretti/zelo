<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
include("../login/php/db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$dados = json_decode(file_get_contents('php://input'), true);

$nome            = isset($dados['nome'])            ? trim($dados['nome'])            : '';
$sobrenome       = isset($dados['sobrenome'])       ? trim($dados['sobrenome'])       : '';
$email           = isset($dados['email'])           ? trim($dados['email'])           : '';
$senha           = isset($dados['senha'])           ? $dados['senha']                 : '';
$data_nasc       = isset($dados['data_nasc'])       ? trim($dados['data_nasc'])       : '';

// Validações
if (empty($nome) || empty($sobrenome) || empty($email) || empty($senha) || empty($data_nasc)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail inválido.']);
    exit;
}

if (strlen($senha) < 8) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'A senha deve ter pelo menos 8 caracteres.']);
    exit;
}

$d = DateTime::createFromFormat('Y-m-d', $data_nasc);
if (!$d || $d->format('Y-m-d') !== $data_nasc) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de nascimento inválida.']);
    exit;
}

if ($d >= new DateTime('today')) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'A data de nascimento deve ser no passado.']);
    exit;
}

// email duplicado (excluir o email atual do usuário)
$stmt = $pdo->prepare('SELECT id FROM cadastro WHERE email = ? AND id != ?');
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.']);
    exit;
}

// atualizar usuário
$hash = password_hash($senha, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('UPDATE cadastro SET nome = ?, sobrenome = ?, email = ?, senha = ?, data_nasc = ? WHERE id = ?');
$stmt->execute([$nome, $sobrenome, $email, $hash, $data_nasc, $user_id]);

echo json_encode(['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso!']);
