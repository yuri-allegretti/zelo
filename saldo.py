import sys
import mysql.connector
from pluggy_api.user_data import get_user_accounts
from zelo.php.addbanco.api_key import get_api_key

def get_item_id_from_db(user_id):
    """Busca o item_id do usuário no banco de dados"""
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="zelo"
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT item_id FROM cadastro WHERE id = %s", (user_id,))
        resultado = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if resultado and resultado.get('item_id'):
            return resultado['item_id']
        return None
    except Exception as e:
        print(f"Erro ao buscar item_id: {e}")
        return None

def main():
    if len(sys.argv) < 2:
        print("Erro: Forneça o user_id como argumento")
        print("Uso: python saldo.py <user_id>")
        return

    user_id = sys.argv[1]
    
    #buscar item_id do usuario no db
    item_id = get_item_id_from_db(user_id)
    
    if not item_id:
        print("Item ID não encontrado para o usuário.")
        return

    api_key = get_api_key()
    contas = get_user_accounts(api_key, item_id)

    if not contas or "results" not in contas or not contas["results"]:
        print("Erro ao buscar contas do usuário")
        return

    #pega primeira conta
    conta = contas["results"][0]

    saldo = conta.get("balance", 0)

    print(saldo)

if __name__ == "__main__":
    main()