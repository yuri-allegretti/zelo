import requests
import mysql.connector
from flask import Flask, jsonify, request, render_template
from dotenv import load_dotenv
import os
from flask_cors import CORS
app = Flask(__name__)
CORS(app)
from api_key import get_api_key
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

load_dotenv()

BASE_URL = "https://api.pluggy.ai"


CLIENT_ID = os.getenv("PLUGGY_CLIENT_ID")
CLIENT_SECRET = os.getenv("PLUGGY_CLIENT_SECRET")

#conectar ao db
def conectar_db():
    return mysql.connector.connect(
        host="localhost",
        port=3307,
        user="root",
        password="",
        database="zelo"
    )

#gerar connect token
@app.route("/connect_token")
def connect_token():
    api_key = get_api_key()
    user_id = request.args.get("userId") #pega userid da url

    if not api_key:
        return {"error": "Falha ao autenticar na Pluggy"}

    response = requests.post(
        f"{BASE_URL}/connect_token",
        headers={
            "X-API-KEY": api_key
        },
        json={
            "clientUserId": user_id
        }, verify=False
    )
    return jsonify(response.json())

#salvar item_id no db
@app.route("/salvar_item", methods=["POST"])
def salvar_item():
    data = request.get_json()

    item_id = data.get("itemId")
    id = data.get("userId")  # 👈 DEFINIR PRIMEIRO

    print("USER_ID RECEBIDO:", id)

    conn = conectar_db()
    cursor = conn.cursor()

    cursor.execute(
        "UPDATE cadastro SET item_id=%s WHERE id=%s",
        (item_id, id)
    )

    conn.commit()
    cursor.close()
    conn.close()

    return {"status": "ok"}

#pegar account_id
@app.route("/accounts/<item_id>")
def accounts(item_id):
    api_key = get_api_key()
    url = f"{BASE_URL}/accounts?itemId={item_id}"

    response = requests.get(url,
        headers={
            "X-API-KEY": api_key
        }, verify=False
    )

    data = response.json()

    if "results" in data and len(data["results"]) > 0:
        account_id = data["results"][0]["id"]
        balance = data["results"][0].get("balance")

        conn = conectar_db()
        cursor = conn.cursor()

        cursor.execute(
            "UPDATE cadastro SET account_id=%s, saldo=%s WHERE item_id=%s",
            (account_id, balance, item_id)
        )

        conn.commit()
        cursor.close()
        conn.close()

        return {"account_id": account_id}

    return {"error": "nenhuma conta encontrada"}


@app.route("/")
def home():
    return render_template("addbanco.html")

if __name__ == "__main__":
    app.run(debug=True)