import os
import requests
from dotenv import load_dotenv
from zelo.php.addbanco.api_key import get_api_key
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

load_dotenv()

BASE_URL = "https://api.pluggy.ai"

api_key = get_api_key()

def get_user_accounts(api_key, item_id):
    url = f"{BASE_URL}/accounts"
    headers = {
        "accept": "application/json",
        "X-API-KEY": api_key
    }
    #itemid como parametro de busca
    params = {
        "itemId": item_id
    }
    response = requests.get(url, headers=headers, params=params, verify=False)

    if response.status_code == 200:
        return response.json() ##retorna a lista de contas
    else:
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
    
    response = requests.get(url, headers=headers, params=params, verify=False)
    
    if response.status_code == 200:
        return response.json() ## Retorna a lista de transações [1, 3]
    else:
        print(f"Erro ao obter transações: {response.status_code}")
        return None