#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <malloc.h>  
#include <sys/types.h>
#include <sys/time.h> 
#include <sys/ipc.h>
#include <sys/shm.h>
#include <sys/stat.h>
#include <unistd.h>
#include "Libraries/AccessLayer.h" // For read files and SHM
#include "Libraries/LogAdministrator.h"


/**
 * Recupera los chunks de los segmentos solicitados y los reconstruye
 * */
 //argv 1 nombre
 //argv 2 segmentsNumber
 //argv 3 rutaSalida
int main (int argc, char ** argv){
	
	//Variables para la bitacora
	struct timeval timerServiceTimeInit, timerServiceTimeEnd;
	struct timeval timerNextFilterInit, timerNextFilterEnd;
	struct LogFile inputFSLog;
	char *logPath = "Logs/KB-MasterSlaveIDADecode.csv";
	char logContent[255];
	char temporalLogContent[50];
	strcpy (temporalLogContent, "");
	strcpy (logContent, "");

	int nextFilterResult = 500;
	
	char *nextFilterName = "PL_IDADecode";
	char *fileName = argv[1];//"20170331_152429_125585_F3_100MB.txt";
	int segmentsNumber = atoi(argv[2]);
	char *rutaSalida = argv[3];
	int milliseconds;

	int metadataSize = 500;//Tamanio de la Metadata Cr1
	
	
	gettimeofday(&timerServiceTimeInit, NULL);
	if(!fileExists(logPath)){
		sprintf(logContent, "NumeroSegmentos, NombreDelArchivo, Comando, TrSiguienteFiltro, TrActual, TamanioArchivo \n");
	}
	
	//Se abre el archivo
	inputFSLog = openLog( logPath );
	if ( inputFSLog.status == 1 ){
		writeLine(inputFSLog, logContent);
		sprintf(logContent, "%s", "");
	} else {
		printf("\t\tALInputFS Error: No se pudo abrir la bitacora\n Favor ");
		printf("de verificar que la ruta %s exista", 
				logPath);
	}
	
	
	if(segmentsNumber == 1) {
		//Datos del servidor	
		nextFilterResult = 0;	
		
		char nextFilterCommand[300]; //nextFilterCommand para invocar al siguiente filtro	
		int segmentosRE = 0; //Segmentos recuperados exitosamente

		//Segmento de memoria compartido para la metadata
		struct shmSegment shm;
		char *metadataContent;
			
		shm = generateShmKeyAndOpenShmSegment(metadataSize);
		
		//Concatenando el nextFilterCommand para ejecutar la peticion del Cr1		
		sprintf ( 
			nextFilterCommand, "./%s %i '%s'", nextFilterName, 
			shm.shmId, fileName
		);
		
		//Ejecutar el siguiente filtro	
		gettimeofday(&timerNextFilterInit, NULL);	
		nextFilterResult = system ( nextFilterCommand );
		gettimeofday(&timerNextFilterEnd, NULL);	
		
		//Guardando los datos en la bitacora
		sprintf(
			logContent, "%s%i, '%s', '%s', ", 
			logContent, segmentsNumber, fileName, 
			nextFilterCommand
		);

		milliseconds = getMilliseconds(timerNextFilterInit, timerNextFilterEnd);
			
		sprintf(logContent, "%s%i, ", logContent, milliseconds);
		
		//Se evalua el resultado
		if(nextFilterResult > 0){	
			printf("-ALInoutFS No existe el archivo\n");
		} else { 
			nextFilterResult = 100;
			//printf("ALInoutFS nextFilterCommand: %s\n", nextFilterCommand);
			//printf("-ALInoutFS The %s file was decoded correctly\n", fileName);
			segmentosRE++;
		}
		

		metadataContent = attach_segment(shm.shmId);

		//Obteniendo el contenido del msj cr1	
		struct contenidoMsjMetadata contenidoMsj;
		contenidoMsj = recuperarElementosMsj(metadataContent);

		//Obteniendo el resultado
		char *processedContent = attach_segment(contenidoMsj.keyMC);
		writeFile(processedContent, contenidoMsj.tamanioDelArchivo, rutaSalida);
		
		//sprintf(logContent, "%s%i\n", logContent, milliseconds);
		
		
		//Vaciando la MC del contenido Cr1
		clean_segment(processedContent, contenidoMsj.keyMC);
		//Vaciando la MC de la metadata
		clean_segment(metadataContent, shm.shmId);
		
		gettimeofday(&timerServiceTimeEnd, NULL);	
		
		milliseconds = getMilliseconds(timerServiceTimeInit, timerServiceTimeEnd);
		sprintf(logContent, "%s%i, ", logContent, milliseconds);
		sprintf(logContent, "%s%ld, ", logContent, contenidoMsj.tamanioDelArchivo);
		
		printf("-ALInoutFS Response time: %ims\n", milliseconds);
		
		if(inputFSLog.status == 1){ 	
			sprintf(logContent, "%s\n", logContent);
			writeLine(inputFSLog, logContent);					
			closeLog(inputFSLog);
		} else {
			printf("Linea de la bitacora:\n%s",logContent);
		}
	} else {
		
		char nextFilterCommand[300];
		strcpy(nextFilterCommand, "");
		char llaves[300];
		strcpy(llaves, "");
		
		struct shmSegment metadataSMSegments[segmentsNumber];
		
		for (int i = 0; i < segmentsNumber; ++i) {
			metadataSMSegments[i] = generateShmKeyAndOpenShmSegment(metadataSize);
			sprintf(llaves, "%s %i", llaves, metadataSMSegments[i].shmId);
		}		
			
		
		//Concatenando el nextFilterCommand para ejecutar la peticion del Cr1		
		sprintf ( 
			nextFilterCommand, "./PL_Integrator '%s' %i %s", 
			fileName, segmentsNumber, llaves
		);
		
		
		sprintf(
			logContent, "%s%i, '%s', '%s', ", 
			logContent, segmentsNumber, fileName, 
			nextFilterCommand
		);
		
		//Ejecutar el siguiente filtro	
		gettimeofday(&timerNextFilterInit, NULL);	
		nextFilterResult = system ( nextFilterCommand );
		gettimeofday(&timerNextFilterEnd, NULL);	
		
		
		milliseconds = getMilliseconds(timerNextFilterInit, timerNextFilterEnd);
		
		
		sprintf(logContent, "%s%i, ", logContent, milliseconds);
		
		if(nextFilterResult == 0){
			printf("All the segments were decoded, starting the integration\n");
			char *contenidoRecuperado;
			long tamanioArchivoRecuperado = 0;
			
			
			long acumuladorBytesCopiados = 0;
			for(int i = 0; i < segmentsNumber; i++){
				
				char *metadataActual; //Contendra la linea con la llave y el tamaño del archivo
				metadataActual = attach_segment(metadataSMSegments[i].shmId);//Se enlaza al SHMS
				struct contenidoMsjMetadata contenidoMsj;//Estructura que contendrá los datos
				
				//Variable que contendrá el contenido separado de la metadata
				contenidoMsj = recuperarElementosMsj(metadataActual);	

				//Reservación del espacio en memoria
				if(i == 0){
					tamanioArchivoRecuperado = contenidoMsj.tamanioDelArchivo * segmentsNumber;
					contenidoRecuperado = (char*) malloc(sizeof(char)*tamanioArchivoRecuperado);
				}
				
				//Se obtiene el contenido del segmento de la SHMS
				char *processedContent = attach_segment(contenidoMsj.keyMC);

				//Concatenando el contenido
				memcpy(contenidoRecuperado+acumuladorBytesCopiados, processedContent, contenidoMsj.tamanioDelArchivo);
				acumuladorBytesCopiados += contenidoMsj.tamanioDelArchivo;
				
				clean_segment(metadataActual, metadataSMSegments[i].shmId);
				clean_segment(processedContent, contenidoMsj.keyMC);

			}

			writeFile(contenidoRecuperado, acumuladorBytesCopiados, rutaSalida);
			free(contenidoRecuperado);
			
			gettimeofday(&timerServiceTimeEnd, NULL);	
		
			milliseconds = getMilliseconds(timerServiceTimeInit, timerServiceTimeEnd);
			
			sprintf(logContent, "%s%i, %ld", logContent, milliseconds, tamanioArchivoRecuperado);
			printf("Response time: %ims\n", milliseconds);
			if(inputFSLog.status == 1){ 	
				sprintf(logContent, "%s\n", logContent);
				writeLine(inputFSLog, logContent);					
				closeLog(inputFSLog);
			} else {
				printf("Linea de la bitacora:\n%s",logContent);
			}
			
		} else {
			printf("Errores\n");
		}
	}
	
		
		
}
