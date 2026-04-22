from dotenv import load_dotenv
import os
import sys
import mysql.connector
from pluggy_api.user_data import get_user_transactions, get_api_key

#config ia
load_dotenv()

#banco
def conectar_db():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="zelo"
    )

def transacao_existe(cursor, tx_id):
    query = "SELECT id FROM transactions WHERE id = %s"
    cursor.execute(query, (tx_id,))
    return cursor.fetchone() is not None

def inserir_transacao(cursor, user_id, account_id, t):
    query = """
    INSERT INTO transactions
    (id, user_id, account_id, amount, description, date, categoria, categoria_editada)
    VALUES (%s, %s, %s, %s, %s, %s, NULL, NULL)
    """

    cursor.execute(query, (
        t["id"],
        user_id,
        account_id,
        t["amount"],
        t.get("description", ""),
        t.get("date", None)
    ))

#main
def main():
    # recebe argumentos do PHP
    if len(sys.argv) < 3:
        print("Uso: python transactions.py <user_id> <account_id>")
        return

    try:
        user_id = int(sys.argv[1])
    except ValueError:
        print("Erro: user_id invalido")
        return

    account_id = sys.argv[2]

    api_key = get_api_key()
    dados = get_user_transactions(api_key, account_id)

    if not dados:
        print("Erro ao buscar transacoes")
        return

    transacoes = dados["results"]

    db = conectar_db()
    cursor = db.cursor()

    novas = 0

    for t in transacoes:
        if not transacao_existe(cursor, t["id"]):
            inserir_transacao(cursor, user_id, account_id, t)
            novas += 1

    db.commit()
    cursor.close()
    db.close()

    print(f"{novas} novas transacoes inseridas em {user_id}")

if __name__ == "__main__":
    main()