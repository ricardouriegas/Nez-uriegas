#!/usr/bin/python3
from app import initialize_app

if __name__ == '__main__':
    app = initialize_app()
    # print(app.config)
    app.run(host='0.0.0.0', port=5000)