#include <stdio.h>
#include <stdlib.h>
#include <string.h>    
#include <unistd.h>
#include <errno.h>
#include <malloc.h>   
#include <libgen.h>
#include <sys/types.h>
#include <sys/time.h> 
#include <pthread.h>
#include "Libraries/AccessLayer.h" // For read files and SHM

/**
 * Declaracion estructuras
 * */
struct thread_data {
	char* nombreArchivo;
	int numeroSegmentos; 
	int segmentoActual;
	int idMemoriacFanterior;
	long tamanioArchivo;
};


/**
 * Declaracion de variables globales
 * */
/*-----------------Memoria----------------------*/
    //Definiciones obligatorias para la memoria compartida	(Entrada)
	int shmIdompartidaCliente;
	long tamanioArchivo;
	int shmId;

/*-----------------Memoria----------------------*/
    //Definiciones obligatorias para la memoria compartida	(Salida)
    int shmIdompartidaSalida;
    char * punteroLocalShmSalida;
    long tamanioArchivoSalida;
    
/**
 * Realiza la segmentacion en un hilo
 * */
void *Segmentar(void *threadarg) {
	//Declaracion de variables locales
	char *nombreArchivo;
	int numeroSegmentos; 
	int segmentoActual;
	long tamanioArchivo;
	int idMemoriacFanterior;
	char nuevoNombre[280];  
	char comandoEjecucion[255];
	struct thread_data *my_data;
	struct Archivo resultado;
	
	//Asignacion de valores a las variables locales
	my_data = (struct thread_data *) threadarg;
	nombreArchivo = my_data->nombreArchivo;
	numeroSegmentos = my_data->numeroSegmentos;
	segmentoActual = my_data->segmentoActual;
	tamanioArchivo = my_data->tamanioArchivo;
	idMemoriacFanterior = my_data->idMemoriacFanterior;

	char *sharedMemoryContent;
	
	//Leyendo el contenido por segmentar
	sharedMemoryContent = attach_segment(idMemoriacFanterior);
	
	//Se realiza la segmentación del archivo
	resultado = segmentarContenidoArchivo(
		sharedMemoryContent, tamanioArchivo, numeroSegmentos, segmentoActual
	);	
	
	//Se limpia el contenido del segmento compartido
	clean_segment(sharedMemoryContent, idMemoriacFanterior);


	struct shmSegment shm; 
	char *segmentContent;

	//Generando el Shared MS para almacenar el segmento
	shm = generateShmKeyAndOpenShmSegment(resultado.tamanioBytes);
	segmentContent = attach_segment(shm.shmId);
	
	//Copiando el contenidos al segmento de MC
	copyFromLocalToShm (
		segmentContent, resultado.contenido, 
		resultado.tamanioBytes
	);

	free(resultado.contenido);
		
	//char *miNombre = my_data->nombreSalida;
	sprintf(nuevoNombre, "%s.%03i", nombreArchivo, segmentoActual);
	
	//Concatenando el comando para ejecutar guardar el segmento
	
	sprintf(
		comandoEjecucion, "./PL_Ida_O1FS4N %ld %i %s", 
		resultado.tamanioBytes, shm.shmId, 
		nuevoNombre
	);
	
	
	//Ejecutando el comando del siguiente filtro
	int resultadoSalida = 0;
	resultadoSalida = system(comandoEjecucion);
	if(resultadoSalida != 0){
		printf("\t¡ERROR: archivo %s no enviado!\n", nuevoNombre);
	}
	//Preparar el segmento de MC usado para su eliminacion
	clean_segment(segmentContent, shm.shmId);
	
	
	//Vaciando el contenido de las estructuras
	memset(&resultado, 0, sizeof resultado);
	//memset(&my_data, 0, sizeof my_data);
	pthread_exit(NULL);
}

/**
 * Funcion principal que invoca los demas metodos
 * */
int main(int argc, char *argv[]) {
	
	
	
	if (argc != 5) {
		//fileSize, 
		//tid, rutaSalida, numeroSegmentos
		fprintf(stderr, "Usage: fileSize idMC nombre numeroSegmentos\n");
		return 2;
	}
	
	//Parametros aplicacion
	int numeroSegmentos;
	long tamanioArchivo = atol(argv[1]);
	int shmId = atoi(argv[2]);
	char *nombreArchivo = argv[3];
	numeroSegmentos = atoi(argv[4]);
	
	/*-----------------Salida-----------------------*/
	//Definiciones requeridas para la salida de los dados
	struct thread_data hilos[numeroSegmentos];
	pthread_t threads[numeroSegmentos];
				
	int rc;
	
	for ( int sA = 1; sA <= numeroSegmentos; sA++ ) {	
		hilos[sA-1].nombreArchivo = nombreArchivo;			
		hilos[sA-1].numeroSegmentos = numeroSegmentos;				
		hilos[sA-1].segmentoActual = sA;
		hilos[sA-1].idMemoriacFanterior = shmId;
		hilos[sA-1].tamanioArchivo = tamanioArchivo;	
		//Creando el hilo
		rc = pthread_create ( 
			&threads[sA-1], NULL, Segmentar, 
			(void *) &hilos[sA-1]
		);
			
		if (rc) {
			printf (
				"ERROR; return code from pthread_create() is %d\n", rc
			);
			exit(-1);
		}
		
	}
	
	pthread_exit(NULL);
	

	return 0;
}
