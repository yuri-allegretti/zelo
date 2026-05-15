const btn      = document.getElementById('btn-editar-user');
const msgBox   = document.getElementById('mensagem');

function mostrarMensagem(texto, tipo) {
    msgBox.textContent = texto;
    msgBox.className   = 'mensagem ' + tipo; // 'erro' ou 'sucesso-msg'
}

function validarCampos(nome, sobrenome, email, senha, data_nasc) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarMensagem('Informe um e-mail válido.', 'erro');
        return false;
    }

    if (senha.length < 8) {
        mostrarMensagem('A senha deve ter pelo menos 8 caracteres.', 'erro');
        return false;
    }

    return true;
}

btn.addEventListener('click', async () => {
    const nome            = document.getElementById('nome').value.trim();
    const sobrenome       = document.getElementById('sobrenome').value.trim();
    const email           = document.getElementById('email').value.trim();
    const senha           = document.getElementById('senha').value;
    const data_nasc       = document.getElementById('data_nascimento').value;

    if (!validarCampos(nome, sobrenome, email, senha)) return;

    if (!data_nasc) {
        mostrarMensagem('Informe sua data de nascimento.', 'erro');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Aguarde...';

    try {
        const response = await fetch('../php/editar_user.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ nome, sobrenome, email, senha, data_nasc })
        });

        const dados = await response.json();
        if (dados.sucesso) {
            mostrarMensagem(dados.mensagem, 'sucesso-msg');
            setTimeout(() => {
                window.location.href = './index.php?page=listar';
            }, 1200);
        } else {
            mostrarMensagem(dados.mensagem, 'erro');
            btn.disabled    = false;
            btn.textContent = 'Enviar';
        }
    } catch (err) {
        mostrarMensagem('Erro de conexão. Tente novamente.', 'erro');
        btn.disabled    = false;
        btn.textContent = 'Enviar';
    }
});
