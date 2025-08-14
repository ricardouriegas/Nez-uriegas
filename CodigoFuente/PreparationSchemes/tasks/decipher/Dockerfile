FROM ddomizzi/decipher:v1

ADD ./app /app

WORKDIR /app

#RUN apt-get update && apt-get install -y python3 python3-pip

#RUN pip3 install -r requirements.txt
EXPOSE 5000
ENTRYPOINT [ "python3", "main.py" ]
