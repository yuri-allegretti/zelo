<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelo - Conectar Banco</title>
    <link rel="stylesheet" href="../../login/css/style.css?v=2.0">
    <script src="https://cdn.pluggy.ai/pluggy-connect.js"></script>
    <style>
        .tela-login { text-align: center; }
        .botoes-container { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .btn-voltar { background-color: #555; box-shadow: 0 4px 15px rgba(85, 85, 85, 0.3); }
        .btn-voltar:hover { background-color: #666; box-shadow: 0 6px 20px rgba(85, 85, 85, 0.5); }
    </style>
</head>
<body>

<div class="tela-login">
    <h1 class="login-titulo">Conectar Banco</h1>
    <p>Conecte sua conta bancária de forma segura para utilizar os recursos.</p>
    
    <div class="botoes-container">
        <button onclick="abrirPluggy()">Conectar banco</button>
        <button class="btn-voltar" onclick="window.history.back()">Voltar</button>
    </div>
</div>

<script>
const USER_ID = <?php echo $_SESSION['user_id']; ?>;

function fecharPluggy(connect) {
    if (connect && typeof connect.close === 'function') {
        connect.close();
        return;
    }

    if (connect && typeof connect.destroy === 'function') {
        connect.destroy();
        return;
    }

    window.location.reload();
}

async function abrirPluggy() {
    const response = await fetch("http://localhost:5000/connect_token");
    const data = await response.json();

    let connect = null;

    connect = new PluggyConnect({
        connectToken: data.accessToken,

        onSuccess: async (itemData) => {
            const itemId = itemData.item.id;

            try {
                await fetch("http://localhost:5000/salvar_item", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({
                        itemId: itemId,
                        userId: USER_ID
                    })
                });

                const accountsResponse = await fetch(`http://localhost:5000/accounts/${itemId}`);
                const accounts = await accountsResponse.json();
                console.log(accounts);
            } finally {
                fecharPluggy(connect);
            }
        },

        onClose: () => {
            fecharPluggy(connect);
        },

        onError: (error) => {
            console.error("Erro no Pluggy Connect:", error);
            fecharPluggy(connect);
        }
    });

    connect.init();
}
</script>

</body>
</html>