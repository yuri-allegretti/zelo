import os
import requests
from dotenv import load_dotenv

load_dotenv()

BASE_URL = "https://api.pluggy.ai"


def get_api_key():
    url = f"{BASE_URL}/auth"
    payload = {
            "clientId": os.getenv("PLUGGY_CLIENT_ID"),
            "clientSecret": os.getenv("PLUGGY_CLIENT_SECRET")
        }
    response = requests.post(url, json=payload)
    if response.status_code == 200:
        return response.json().get("apiKey")
    else:
        print("Erro na autenticação:", response.status_code)
        return None

api_key = get_api_key()

def get_user_accounts(api_key, item_id):
    url = f"{BASE_URL}/accounts"
    headers = {
        "accept": "application/json",
        "X-API-KEY": api_key
    }
    # Adicionando o itemId como parâmetro de busca
    params = {
        "itemId": item_id
    }
    response = requests.get(url, headers=headers, params=params)
    
    if response.status_code == 200:
        return response.json()
    else:
        # Se retornar 400 aqui, verifique se o item_id é válido e se a sincronização terminou
        print(f"Erro ao obter contas: {response.status_code}")
        print("Detalhes:", response.text)
        return None

def get_user_transactions(api_key, account_id):
    url = f"{BASE_URL}/transactions"
    headers = {
        "accept": "application/json",
        "X-API-KEY": api_key
    }
    # Filtramos as transações por uma conta específica
    params = {"accountId": account_id}
    
    response = requests.get(url, headers=headers, params=params)
    
    if response.status_code == 200:
        return response.json() # Retorna a lista de transações [1, 3]
    else:
        print(f"Erro ao obter transações: {response.status_code}")
        return None