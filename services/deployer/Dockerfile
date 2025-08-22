FROM ddomizzi/deployer:normas

WORKDIR /home/app/

ADD ./app .

RUN cd puzzlemesh && make

RUN apt-get update

# RUN python3 -m pip install --upgrade pip
# RUN python3 -m pip install --upgrade Pillow
# RUN python3 -m pip install --upgrade matplotlib

# #RUN DEBIAN_FRONTEND=noninteractive apt-get install -y python3-pil.imagetk 
# #RUN DEBIAN_FRONTEND=noninteractive apt-get install -y  python3-matplotlib

# RUN pip3 install --no-cache-dir -r requirements.txt

# RUN pip3 install plotly==5.3.1
# RUN pip3 install -U kaleido
