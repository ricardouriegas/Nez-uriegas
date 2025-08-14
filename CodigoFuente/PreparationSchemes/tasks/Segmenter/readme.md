# Demostración	

Patrones de Kulla para la segmentación-codificación y decodificación-integración de los datos.

* **KB-SegmentadorIDA/**: Código para realizar la recolección, decodificación e integración de datos.
* **KB-IntegradorIDA/**: Código para realizar la segmentación y codificación de datos.
* **01_CrearImagenesContenedor.sh**: Permite realizar la creación de las imágenes de contenedor a través de los archivos Dockerfile.
* **02_CrearContenedorOrigen.sh**: Crea una instancia de contenedor de codificación
* **03_CrearContenedorDestino.sh**: Crea una instancia de contenedor de decodificación
* **DockerfileKBIntegradorIDA**: Archivo que permite crear una imagen de contenedor con el código de  **KB-IntegradorIDA/**.
* **DockerfileKBSegmentadorIDA**:  Archivo que permite crear una imagen de contenedor con el código de  **KB-SegmentadorIDA/**.
* **DockerfileKullaBase**: Crear una imagen de contenedor con las dependencias de Kulla.
* **readme.md**: Este archivo.

## Creación de las imágenes de contenedor

Es necesario crear tres imágenes de contenedor

* **kulla:base**: Contiene las dependencias de Kulla y de las aplicaciones.
* **kulla:code**: Contiene el patrón *Divide&Containerize* de Kulla que realiza la segmentación de los archivos conectado con un filtro de codificación.
* **kulla:decode**: Contiene el patrón *Divide&Containerize* que realiza la decodificación de los segmentos y realiza la integración del contenido.

Para realizar la creación de las imágenes de contenedor se pueden utilizar los siguientes comandos:

```bash
docker build -f DockerfileKullaBase -t "kulla:base" .
docker build -f DockerfileKBSegmentadorIDA -t "kulla:code" .
docker build -f DockerfileKBIntegradorIDA -t "kulla:decode" .
```

O también puede utilizarse el script *01_CrearImagenesContenedor.sh* utilizando el comando:

```bash
sh 01_CrearImagenesContenedor.sh
```

## Creación del contenedor origen

Es el contenedor encargado de realizar la segmentación del archivo seleccionado en *n* segmentos y realizar la codificación de cada segmento.

El contenedor **origen** requiere que se le asignen **seis** volúmenes virtuales.

* */home/Volume/Up/*: Es la carpeta del contenedor origen enlazada a la carpeta del *host* que contiene los datos que deben ser codificados.
* */home/Volume/chunk1/*: Es la carpeta que almacenará el *chunk1* producido por el algoritmo de codificación ida. Deberá estar enlazada a una sub-carpeta del sistema de  publicación/suscripción encargado de realizar el transporte de los datos.
* */home/Volume/chunk2/*: Es la carpeta que almacenará el *chunk2* producido por el algoritmo de codificación ida. Deberá estar enlazada a una sub-carpeta del sistema de  publicación/suscripción encargado de realizar el transporte de los datos.
* */home/Volume/chunk3/*: Es la carpeta que almacenará el *chunk3* producido por el algoritmo de codificación ida. Deberá estar enlazada a una sub-carpeta del sistema de  publicación/suscripción encargado de realizar el transporte de los datos.
* */home/Volume/chunk4/*: Es la carpeta que almacenará el *chunk4* producido por el algoritmo de codificación ida. Deberá estar enlazada a una sub-carpeta del sistema de  publicación/suscripción encargado de realizar el transporte de los datos.
* */home/Volume/chunk5/*: Es la carpeta que almacenará el *chunk5* producido por el algoritmo de codificación ida. Deberá estar enlazada a una sub-carpeta del sistema de  publicación/suscripción encargado de realizar el transporte de los datos.

### Comando de lanzamiento del contenedor origen

El comando requerido para el lanzamiento del contenedor **origen** es el siguiente:

```bash
docker run -ti -d --name origen \
	-v /home/hreyes/kulla/Carga/Up/:/home/Volume/Up/ \
	-v /home/hreyes/kulla/Carga/c1/:/home/Volume/chunk1/ \
	-v /home/hreyes/kulla/Carga/c2/:/home/Volume/chunk2/ \
	-v /home/hreyes/kulla/Carga/c3/:/home/Volume/chunk3/ \
	-v /home/hreyes/kulla/Carga/c4/:/home/Volume/chunk4/ \
	-v /home/hreyes/kulla/Carga/c5/:/home/Volume/chunk5/ \
kulla:code
```

El comando anterior se encuentra dentro del archivo *02_CearContenedorOrigen.sh*. Es necesario que modifique las rutas de los volúmenes de datos por las del equipo en el que sera lanzado el contenedor. 

### Creación del contenedor destino

Es el contenedor encargado de realizar la decodificación de los *m* segmentos requeridos por el algoritmo IDA y la integración de los mismos con el fin de obtener el contenido original.

El contenedor **destino** requiere que se le asignen **seis** volúmenes virtuales.

- */home/Volume/Results/chunk1/*: Es la carpeta que almacenará el *chunk1* descargado a través del sistema de  publicación/suscripción.
- */home/Volume/Results/chunk2/*: Es la carpeta que almacenará el *chunk2* descargado a través del sistema de  publicación/suscripción.
- */home/Volume/Results/chunk3/*: Es la carpeta que almacenará el *chunk3* descargado a través del sistema de  publicación/suscripción.
- */home/Volume/Results/chunk4/*:Es la carpeta que almacenará el *chunk4* descargado a través del sistema de  publicación/suscripción.
- */home/Volume/Results/chunk5/*:Es la carpeta que almacenará el *chunk5* descargado a través del sistema de  publicación/suscripción.
- /home/Volume/Down/:Es la carpeta que almacenará los archivos decodificados utilizando los chunks recuperados por el sistema de publicación/suscripción.

El comando requerido para el lanzamiento del contenedor **destino** es el siguiente:

```bash
docker run -ti -d --name destino \
	-v /home/hreyes/kulla/Descarga/c1/:/home/Volume/Results/chunk1/ \
	-v /home/hreyes/kulla/Descarga/c2/:/home/Volume/Results/chunk2/ \
    -v /home/hreyes/kulla/Descarga/c3/:/home/Volume/Results/chunk3/ \
    -v /home/hreyes/kulla/Descarga/c4/:/home/Volume/Results/chunk4/ \
    -v /home/hreyes/kulla/Descarga/c5/:/home/Volume/Results/chunk5/ \
	-v /home/hreyes/kulla/Descarga/Down/:/home/Volume/Down/ \
kulla:decode
```

El comando anterior se encuentra dentro del archivo *03_CearContenedorDestino.sh*. Es necesario que modifique las rutas de los volúmenes de datos por las del equipo en el que sera lanzado el contenedor. 

### Ejemplos de utilización

A continuación se listan las formas de enviar trabajo a los contenedores de **origen** y **destino**.

#### Origen 

Para indicarle al contenedor *origen* que realice la codificación se ejecuta el siguiente comando:

```
docker exec -ti origen ./ALInputFS /home/Volume/Up/nombreArchivo.extension numeroSegmentos
```

Por ejemplo:

```
docker exec -ti origen ./ALInputFS /home/Volume/Up/F3.txt 1
docker exec -ti origen ./ALInputFS /home/Volume/Up/F3.txt 2
```

#### Destino

Para realizar la decodificación de un archivo se ejecuta el siguiente comando:

```
docker exec -ti destino ./ALInputFS nombreArchivo.extension numeroSegmentos /home/Volume/Down/NombreSalida.extensión.
```

Por ejemplo:

```
docker exec -ti destino ./ALInputFS F3.txt 1 /home/Volume/Down/F3_1.txt
docker exec -ti destino ./ALInputFS F3.txt 2 /home/Volume/Down/F3_2.txt

```

**NOTA:** Es necesario que los chunks requeridos para la decodificación del archivo solicitado ya hayan sido descargados y colocados en el interior de las carpetas correspondientes. El número de segmentos debe de ser igual al utilizado para la codificación.