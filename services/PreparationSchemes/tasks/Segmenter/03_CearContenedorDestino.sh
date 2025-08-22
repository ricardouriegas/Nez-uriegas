#Crear contenedor destino
#
#Reemplazar /home/hreyes/kulla/Descarga por la ruta de tu PC
docker run -ti --name destino \
	-v /home/hreyes/kulla/Descarga/c1/:/home/Volume/Results/chunk1/ \
	-v /home/hreyes/kulla/Descarga/c2/:/home/Volume/Results/chunk2/ \
    -v /home/hreyes/kulla/Descarga/c3/:/home/Volume/Results/chunk3/ \
    -v /home/hreyes/kulla/Descarga/c4/:/home/Volume/Results/chunk4/ \
    -v /home/hreyes/kulla/Descarga/c5/:/home/Volume/Results/chunk5/ \
	-v /home/hreyes/kulla/Descarga/Down/:/home/Volume/Down/ \
kulla:decode
