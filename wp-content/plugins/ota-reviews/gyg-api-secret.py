#!/usr/bin/python3
import requests
import base64

# API ENDPOINT SPECIFICATION
# https://code.getyourguide.com/partner-api-spec/#/Suppliers/Suppliers
# ENDPOINT_URL = "https://api.getyourguide.com/1/" #TEST
ENDPOINT_URL = "https://api.getyourguide.com/"
TEST_ENDPOINT_URL = "https://api.gygtest.net/"
#Notify Availability Update - Documentation
#TEST_ENDPOINT_URL = "https://supplier-api.getyourguide.com/sandbox/1/notify-availability-update"

# def get_product(header_auth):
#     response = requests.get(ENDPOINT_URL, headers=header_auth)
#     print(response.text)
#     return response.json()


def main():
    clientid="contact@khaolaklanddiscovery.com"
    client_secret="*KLLD076485411Khaolak"
    print('official integrator gyg api auth token')
    token = base64.b64encode(f"{clientid}:{client_secret}".encode()).decode()
    header_auth = {'Authorization': 'Basic '+ token}

    print("Token:",token)

    header_auth = {'Authorization': token}

    print("Header:",header_auth)
    #print(header_auth)
    #print(get_product(header_auth))
    path = ENDPOINT_URL+'products'

    req = requests.get(path, headers=header_auth)
    print(req.text)

if __name__ == "__main__":
    main()