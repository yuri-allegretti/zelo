<?php
 switch ($_REQUEST["acao" ]) {
    case 'cadastrar':
        $nome = $_POST["nome"];
        $email = $_POST["email"];
        $senha = $_POST["senha"];
        $data_nasc = $_POST["data_nasc"];

        $sql = "INSERT INTO cadastro (nome, email, senha, data_nasc) VALUES ('{$nome}', '{$email}', '{$senha}', '{$data_nasc}')";

        $res = $conn->query($sql);
        break;
    
    case 'editar':
         $nome = $_POST["nome"];
        $email = $_POST["email"];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $data_nasc = $_POST["data_nasc"];

        $sql = "UPDATE cadastro SET
                nome='{$nome}',
                email='{$email}',
                senha='{$senha}',
                data_nasc='{$data_nasc}'
                WHERE id=".$_REQUEST["id"];

        $res = $conn->query($sql);

        break;

    case 'excluir':
       $sql = "DELETE FROM cadastro WHERE id=".$_REQUEST["id"];
        $res = $conn->query($sql);
        session_destroy(); 
        header('Location: ../login/html/cadastro.html');
    exit;
        break;

 }