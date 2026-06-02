const btn    = document.getElementById('btn-login');
const msgBox = document.getElementById('mensagem');

function mostrarMensagem(texto, tipo) {
    msgBox.textContent = texto;
    msgBox.className   = 'mensagem ' + tipo; // erro ou sucesso-msg
}

btn.addEventListener('click', async () => {
    const email = document.getElementById('email').value;//ap
    const senha = document.getElementById('senha').value;

    if (!email || !senha) {//ap
        mostrarMensagem('Preencha todos os campos.', 'erro');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Entrando...';

    try {
        const response = await fetch('../php/login.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body:    JSON.stringify({email, senha})//ap
        });

        const dados = await response.json();

        if (dados.sucesso) {
            mostrarMensagem(dados.mensagem, 'sucesso-msg');
           
            localStorage.setItem('cadastro', JSON.stringify(dados.usuario));
            setTimeout(() => {
                if (dados.usuario.nivel === 'suporte'){
                    window.location.href = '../../../Suporte/suporte.php'
                } else {
                    window.location.href = '../../php/index.php'
                }
            }, 1000);
        } else {
            mostrarMensagem(dados.mensagem, 'erro');
            btn.disabled    = false;
            btn.textContent = 'Entrar';
        }
    } catch (err) {
        mostrarMensagem('Erro de conexão. Tente novamente.', 'erro');
        btn.disabled    = false;
        btn.textContent = 'Entrar';
    }
});

