<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

$nome            = isset($dados['nome'])            ? trim($dados['nome'])            : '';
$email           = isset($dados['email'])           ? trim($dados['email'])           : '';
$senha           = isset($dados['senha'])           ? $dados['senha']                 : '';
$data_nasc       = isset($dados['data_nasc'])       ? trim($dados['data_nasc'])       : '';

// Validações
if (empty($nome) || empty($email) || empty($senha) || empty($data_nasc)) {
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

// email duplicado
$stmt = $pdo->prepare('SELECT id FROM cadastro WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.']);
    exit;
}

// nivel user
$hash = password_hash($senha, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO cadastro (nome, email, senha, data_nasc) VALUES (?, ?, ?, ?)');
$stmt->execute([$nome, $email, $hash, $data_nasc]);

echo json_encode(['sucesso' => true, 'mensagem' => 'Conta criada com sucesso!']);
