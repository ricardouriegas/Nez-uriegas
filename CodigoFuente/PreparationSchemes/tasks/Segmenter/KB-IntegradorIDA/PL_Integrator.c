#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <malloc.h>   
#include <libgen.h>
#include <sys/types.h>
#include <sys/time.h> 
#include <unistd.h>
#include <pthread.h>
#include "Libraries/AccessLayer.h" 
#include "Libraries/LogAdministrator.h"
/**
 * Contiene los datos que son pasados por el hilo principal
 * a cada hilo creado
 * */
struct thread_data {
	key_t metadataShmId;
	int actualSegmentNumber;
	char *outputFileName;
};
    

int metadataSize = 500;

/**
 * Realiza la solicitud de decodificación al filtro de IDA
 * Esta función es ejecutada en un hilo
 * @param  threadarg estructura que contiene los parámetos de
 * de cada hilo
 */
void * rebuildSegment(void *threadarg) {
	
	
	//Tiempo de servicio de la peticion get
	struct timeval inicioGet, finGet;
	struct timeval inicioSalida, finSalida;
	int milliseconds = 0;
	gettimeofday(&inicioGet, NULL);
	
	//Variables para la bitacora
	struct LogFile integratorLog;
	char *integratorLogPath = "Logs/PL_Integrator.csv";
	char logContent[255];
	char temporalLogContent[50];
	strcpy (temporalLogContent, "");
	strcpy (logContent, "");
	
	//Declaracion de variables locales
	char *nextFilterName = "PL_IDADecode";
	int actualSegmentNumber;
	char executionCommandNextFilter[255];
	char *outputFileName;
	char segmentOutputName[100];
	struct thread_data *my_data;
	int nextFilterResult = 500;
	key_t metadataShmId;
	
	//Contenido de la metadata para el filtro anterior
	char *metadataPreviousFilter;

	//SHMSegment for the metadata of the next filter
	struct shmSegment structMetadataNextFilter;;
	
	
	//Se abre el archivo
	integratorLog = openLog( integratorLogPath );
	if ( integratorLog.status == 1 ){
		//writeLine(integratorLog, logContent);					
		sprintf (logContent, "%s", "GET, ");
	} else {
		printf ( "Error: No se pudo abrir la bitacora\n Favor " );
		printf ( "de verificar que la ruta %s exista", integratorLogPath );
	}
	
	//Asignacion de valores a las variables locales
	my_data = (struct thread_data *) threadarg;
	actualSegmentNumber = my_data->actualSegmentNumber;
	metadataShmId = my_data->metadataShmId;
	outputFileName = my_data->outputFileName;
	strcpy(segmentOutputName, "");

	//Enlazando la variable local al SHMSegment de la metadata
	metadataPreviousFilter = attach_segment(metadataShmId);

	//Creando el SHMSegment para la metadata del siguiente filtro
	char *metadataNextFilter;
	structMetadataNextFilter = generateShmKeyAndOpenShmSegment(metadataSize);
	
	//Se le añade el numero de segmento al nombre del archivo
	sprintf(segmentOutputName, "%s.%03i", outputFileName, actualSegmentNumber);
	//Se agregan los datos a la bitacora
	sprintf(logContent, "%s%i, %s, %s, ",logContent, actualSegmentNumber, outputFileName, segmentOutputName);
	
	//Concatenando el executionCommandNextFilter para ejecutar la peticion del Cr1		
	sprintf ( 
		executionCommandNextFilter, "./%s %i %s", nextFilterName, 
		structMetadataNextFilter.shmId, segmentOutputName
	);
	
	//Ejecutar el siguiente filtro	
	gettimeofday(&inicioSalida, NULL);	
	nextFilterResult = system ( executionCommandNextFilter );
	gettimeofday(&finSalida, NULL);
	
	milliseconds = getMilliseconds(inicioSalida, finSalida);
	
	sprintf(logContent, "%s%i, ", logContent, milliseconds);
	
	//Se evalua el resultado
	if(nextFilterResult > 0){	
		printf("\tIntegrador No existe el archivo\n");
	} else { 
		nextFilterResult = 100;
		//printf("\tIntegrador executionCommandNextFilter: %s\n", executionCommandNextFilter);
		//printf("\tIntegrador %s si existe\n", segmentOutputName);
	}

	//Se enlaza el contenido del SHMSegment con la metadata del filtro
	//siguiente a una variable local
	metadataNextFilter = attach_segment(structMetadataNextFilter.shmId);

	//Copiando la metadada del filtro siguiente al anterior
	copyFromLocalToShm(
			metadataPreviousFilter, metadataNextFilter, metadataSize
	);

	
	
	gettimeofday(&finGet, NULL);
	milliseconds = getMilliseconds(inicioGet, finGet);
	
	sprintf(logContent, "%s%i", logContent, milliseconds);	
	
	if(integratorLog.status == 1){ 	
		sprintf(logContent, "%s\n", logContent);
		writeLine(integratorLog, logContent);					
		closeLog(integratorLog);
	} else {
		printf("Linea de la bitacora:\n%s",logContent);
	}
	
	clean_segment(metadataNextFilter, structMetadataNextFilter.shmId);

		
	memset(&my_data, 0, sizeof my_data);
	pthread_exit(NULL);
}

/**
 * Funcion principal que invoca los demas metodos
 * */
int main(int argc, char *argv[]) {
	
	//Parametros aplicacion
	int numeroSegmentos; //Numero de segmentos por utilizar
	char *nombreBase;
	nombreBase = argv[1];
	numeroSegmentos = atoi(argv[2]);	
	key_t shmIds[numeroSegmentos];
	
	/*-----------------Salida-----------------------*/
	//Definiciones requeridas para la salida de los dados
	struct thread_data hilos[numeroSegmentos]; //Parametros hilos
	pthread_t threads[numeroSegmentos]; // hilos
		
	char *soloNombre;
	char nuevoNombre[100];
	strcpy(nuevoNombre, "");	
	
	//Obteniendo las llaves
	for(int i = 0; i < numeroSegmentos; i++){
		shmIds[i] = atoi(argv[3+i]);
	}
	
	soloNombre = basename(nombreBase);
    
				
	int rc;
	
	for ( int sA = 1; sA <= numeroSegmentos; sA++ ) {	

		hilos[sA-1].actualSegmentNumber = sA;		
		hilos[sA-1].outputFileName = soloNombre;	
		hilos[sA-1].metadataShmId = shmIds[sA -1];	
		//Creando el hilo
		rc = pthread_create ( 
			&threads[sA-1], NULL, rebuildSegment, 
			(void *) &hilos[sA-1]
		);
			
		if (rc) {
			printf (
				"--Integrador ERROR; return code from pthread_create() is %d\n", rc
			);
			exit(-1);
		}
		
	}
	
	pthread_exit(NULL);
	return 0;
}