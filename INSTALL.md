# 🚀 Instalar Dependências do Projeto Zelo

## Para computadores novos:

### 1. Abra o PowerShell e navegue até a pasta do projeto:
```powershell
cd C:\xampp\htdocs\zelo\ sprint 1\zelo
```

### 2. Crie um ambiente virtual (recomendado):
```powershell
python -m venv venv
```

### 3. Ative o ambiente virtual:
```powershell
.\venv\Scripts\Activate
```

### 4. Instale todas as dependências de uma vez:
```powershell
pip install -r requirements.txt
```

## ✅ Pronto!

Agora você tem todas as bibliotecas instaladas para rodar o projeto.

### Para rodar o Flask (página de conectar banco):
```powershell
python zelo\php\addbanco\connect_token.py
```

### Para rodar outros scripts Python:
```powershell
python transactions.py
python saldo.py
```

---

## 📋 Bibliotecas incluídas:

- **python-dotenv** - Carrega variáveis do `.env`
- **requests** - Requisições HTTP para API Pluggy
- **mysql-connector-python** - Conexão com banco MySQL
- **Flask** - Framework web para o servidor
- **flask-cors** - Suporte CORS no Flask
- **urllib3** - HTTP client (vem com requests, incluído por segurança)

---

## ⚠️ Lembre-se:

- Garanta que o MySQL está rodando
- O arquivo `.env` está com as credenciais corretas do Pluggy
- A pasta do banco de dados `zelo` existe no MySQL
