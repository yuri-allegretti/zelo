<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

$nome  = isset($dados['nome'])  ? trim($dados['nome'])  : '';
$senha = isset($dados['senha']) ? $dados['senha']       : '';

if (empty($nome) || empty($senha)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nome, senha, nivel FROM cadastro WHERE nome = ?');
$stmt->execute([$nome]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome ou senha incorretos.']);
    exit;
}

session_start();
$_SESSION['user_id'] = $usuario['id'];
$_SESSION['user_nome'] = $usuario['nome'];

$stmt = $pdo->prepare('SELECT account_id FROM cadastro WHERE id = ?');
$stmt->execute([$usuario['id']]);
$dadosConta = $stmt->fetch();

$account_id = $dadosConta['account_id'] ?? null;

if ($account_id) {
    $user_id = (int)$usuario['id'];

    // Base da pasta do projeto: .../teste assistente
    $projectRoot = dirname(__DIR__, 3);
    $pythonScript = $projectRoot . DIRECTORY_SEPARATOR . 'transactions.py';

    // Ajuste o executavel caso necessario no seu ambiente XAMPP/Windows
    $pythonBin = 'python';

    $cmd = sprintf(
        '%s %s %s %s 2>&1',
        escapeshellcmd($pythonBin),
        escapeshellarg($pythonScript),
        escapeshellarg((string)$user_id),
        escapeshellarg((string)$account_id)
    );

    $logFile = $projectRoot . DIRECTORY_SEPARATOR . 'transactions_login.log';

    $bgCmd = 'start "" /B cmd /C "' . $cmd . ' >> ' . escapeshellarg($logFile) . ' 2>&1"';
    exec($bgCmd);

    // Log opcional para depuracao
    // file_put_contents($projectRoot . DIRECTORY_SEPARATOR . 'transactions_login.log', $output . PHP_EOL, FILE_APPEND);
}

echo json_encode([
    'sucesso'  => true,
    'mensagem' => 'Login realizado com sucesso!',
    'usuario'  => ['id' => $usuario['id'], 'nome' => $usuario['nome'], 'nivel' => $usuario['nivel']]
]);
