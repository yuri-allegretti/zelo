<?php
    $id_logado = $_SESSION['user_id'];
    $sql = "SELECT * FROM cadastro WHERE id = {$id_logado}";
    
    $res = $conn->query($sql);
    $qtd = $res->num_rows;

    if($qtd > 0){
        while($row = $res->fetch_object()){
            print "
            <div class='page-container'>
                    <div class='d-flex align-items-center mb-3'>
                        <div class='rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3' style='width:50px;height:50px;font-size:1.4rem;'>
                            ".strtoupper(substr($row->nome, 0, 1))."
                        </div>
                        <h4 class='mb-0'>".$row->nome."</h4>
                    </div>
                    <hr>
                    <p class='mb-2'><strong>ID:</strong> ".$row->id."</p>
                    <p class='mb-2'><strong>E-mail:</strong> ".$row->email."</p>
                    <p class='mb-3'><strong>Data Nasc.:</strong> ".$row->data_nasc."</p>
                    <div class='botoes-container'>
                        <button onclick=\"location.href='?page=editar&id=".$row->id."';\">Editar</button>
                        <a type='link' href='?page=addbanco' class='connect-bank-link'>+ Adicionar banco</a>
                        <button onclick=\"if(confirm('Deseja sair da conta?')){location.href='?page=salvar&acao=logout';}else{false}\"class='btn btn-danger'>Sair</button>
                    </div>
            </div>";
        }
    } else {
        print "<p>Nenhum usuário encontrado.</p>";
    }

    print "
        
    ";
?>
