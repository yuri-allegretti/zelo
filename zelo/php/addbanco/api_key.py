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