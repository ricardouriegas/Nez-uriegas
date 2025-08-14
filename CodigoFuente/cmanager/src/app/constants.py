from os import getenv

USERID = 'userid'
USERNAME = 'username'
USERTOKEN = 'usertoken'
UPLOAD_FOLDER = 'uploads'
JSON_HEADERS = {"Content-Type": "application/json"}

REGISTRY_PAIR = getenv('REGISTRY_HOST', '127.0.0.1') + ':' + str(getenv('REGISTRY_PORT', 5000))
VC_PAIR = getenv('VALUECHAIN_HOST', 'value-chain-api') + ':' + str(getenv('VALUECHAIN_PORT', 80))
GW_PAIR = getenv('GATEWAY_HOST', 'apigateway_local') + ':' + str(getenv('GATEWAY_PORT', 80))