#Crear contenedor origen
#
#Reemplazar /home/hreyes/ por la ruta de tu PC
docker run -ti --name origen \
	-v /home/hreyes/kulla/Carga/Up/:/home/Volume/Up/ \
	-v /home/hreyes/kulla/Carga/c1/:/home/Volume/chunk1/ \
	-v /home/hreyes/kulla/Carga/c2/:/home/Volume/chunk2/ \
	-v /home/hreyes/kulla/Carga/c3/:/home/Volume/chunk3/ \
	-v /home/hreyes/kulla/Carga/c4/:/home/Volume/chunk4/ \
	-v /home/hreyes/kulla/Carga/c5/:/home/Volume/chunk5/ \
kulla:code

#./ALInputFS nombreArchivo.img 1
#./ALInputFS nombreArchivo.img 2
#./ALInputFS nombreArchivo.img Cores
