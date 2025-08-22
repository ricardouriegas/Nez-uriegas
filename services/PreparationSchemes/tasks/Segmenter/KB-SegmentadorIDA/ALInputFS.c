#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>
#include <time.h>
#include <libgen.h> //basename
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/shm.h>
#include <sys/time.h>
#include "Libraries/AccessLayer.h"
#include "Libraries/LogAdministrator.h"

void printusage(char *app){
	printf("Error in the execution parameters\n");
	printf("Correct execution example\n");
	printf("%s pathFile numSegments\n", app );
	printf("\tpathFile: path of the file in te FS\n");
	printf("\tNumSegments: Number of segments to create\n");
}


int main(int argc, char **argv){

	if(argc != 4) {
		printusage(argv[0]);
		return -1;
	}

	//para medir los tiempos
	struct timeval inicioMetatuberia, finMetatuberia;
	struct timeval inicioEntradaDatos, finEntradaDatos;
	struct timeval inicioSalidaDatos, finSalidaDatos;
	struct timeval inicioSegmentador, finSegmentador;


	/** Inicia a contar el tiempo de servicio **/
	gettimeofday(&inicioMetatuberia, NULL);

	char *dataSourcePath = "";
	//Se obtiene del archivo de configurcion
	char *nextFilterName = "";


	char completeFilePath [1000];
	strcpy ( completeFilePath, dataSourcePath );

	//Para la aplicacion
	char * fileContent;
	int segmentNumber;
	int milliseconds = 0;
	char newFileName[1000];
	int nextFilterResult = 0;

	//Para la memoria compartida
	long fileSize;
	struct shmSegment shm;
	char *sharedMemoryContent;

	//Para la bitacora
	struct LogFile AccessLayerLog;
	char *logPath = "Logs/KB-MasterSlaveIDACode.csv";
	char logContent[1000];
	char temporalLogContent[1000];
	strcpy (temporalLogContent, "");
	strcpy(logContent, "");


	//Para obtener la fecha y hora
	time_t tiempo;
	tiempo = time(0);
	struct tm *tlocal;
	char fecha[8];
	char hora[8];

	tlocal = localtime(&tiempo);
	strftime(fecha,8,"%Y%m%d",tlocal);
	strftime(hora,8,"%H%M%S",tlocal);


	char nextFilterCommand[1000];

	sprintf ( temporalLogContent, "%s, %s, ",fecha, hora );
	strcat( logContent, temporalLogContent );

	//Se obtiene el directorio de trabajo de la apliacion actual
	char * cwd;
	cwd = getCurrentWorkDirectry();

	/**
	 * Se inicializa el valor del comando que se usara para
	 * ejecutar el filtro siguiente
	**/
	strcpy(nextFilterCommand, "");
	char * filePath = argv[1];

	strcat(completeFilePath, filePath);
	strcat(logContent, completeFilePath);
	strcat(logContent, ", ");


	char headLine[1000];
	strcpy(headLine, "");
	if(!fileExists(logPath)){
		sprintf(headLine, "Fecha, Hora, PathOrigen, Nombre, Segmentos, TiempoReadData, TiempoWriteMemory ,Comando, TiempoServicioFiltroSiguiente, Trf \n");
	}

	//Se abre el archivo
	AccessLayerLog = openLog( logPath );
	if ( AccessLayerLog.status == 1 ){
		writeLine(AccessLayerLog, headLine);
		//closeLog(AccessLayerLog);
		strcpy(headLine, "");
	} else {
		printf("\t\tSalidaMC2R Error: No se pudo abrir la bitacora\n");
		printf("Verificar que la ruta %s exista", logPath);
	}

	//Se verifica la existencia del archivo solicitado
	if(fileExists(completeFilePath)) {
		//Si existe el archivo
		char * justName =  argv[3];
		segmentNumber = atoi(argv[2]);

		//Agregando valores a la linea que sera ingresada a la bitacora
		sprintf (
			temporalLogContent, "%s , %i, ", justName, segmentNumber
		);

		strcat(logContent, temporalLogContent);

		//Iniciando la lectura del los datos
		gettimeofday(&inicioEntradaDatos, NULL);

		fileSize = getFileSize ( completeFilePath );
		fileContent = readFile( completeFilePath );
		//Finalizando la lectura de los datos
		gettimeofday(&finEntradaDatos, NULL);

		//Calculando el tiempo de entrada de datos
		milliseconds = getMilliseconds(
			inicioEntradaDatos,
			finEntradaDatos
		);

		//printf("Tiempo (T) Lectura datos: %i  ms\n", milliseconds);
		sprintf(temporalLogContent, "%i, ", milliseconds);
		strcat(logContent, temporalLogContent);

		//Inicia el tiempo para la carga de datos en la MC
		gettimeofday(&inicioSalidaDatos, NULL);

		shm = generateShmKeyAndOpenShmSegment(fileSize);
		sharedMemoryContent = attach_segment(shm.shmId);

		//Copiando el contenido del buffer al segmento de MC
		copyFromLocalToShm(sharedMemoryContent, fileContent, fileSize);

		//Se vacia el puntero de entrada
		free ( fileContent );

		//Finaliza el tiempo para la carga de datos en la MC
		gettimeofday(&finSalidaDatos, NULL);

		//Calculando el tiempo de carga de datos a la MC
		milliseconds = getMilliseconds (
			inicioSalidaDatos,
			finSalidaDatos
		);

		//printf("T. Almacenamiento SHM: \t%i  ms\n", milliseconds);

		sprintf( temporalLogContent, "%i, ", milliseconds );
		strcat(logContent, temporalLogContent);

		sprintf (
			newFileName, "%s", justName
		);

		if(segmentNumber != 1){
			//printf("%s\n", "Segmentation and coding process IDA(5,3)");
			printf("%s ", newFileName);
			nextFilterName = "PL_SegmentadorMemoria_SHM";

			sprintf(
				nextFilterCommand,
				"%s/%s %ld %i '%s' %i", cwd, nextFilterName,
				fileSize, shm.shmId,
				newFileName, segmentNumber
			);

			//Concatenando el comando a la bitacora
			sprintf (
				temporalLogContent, " %s, ", nextFilterCommand
			);

			strcat ( logContent, temporalLogContent );

			gettimeofday(&inicioSegmentador, NULL);
			nextFilterResult = system(nextFilterCommand);
			gettimeofday(&finSegmentador, NULL);


			//Preparando el SMS para borrarlo
			clean_segment(
				sharedMemoryContent,
				shm.shmId
			);

		} else {
			nextFilterName = "PL_Ida_O1FS4N";
			printf("%s ", newFileName);
			sprintf(
				nextFilterCommand,
				"%s/%s %ld %i '%s'", cwd, nextFilterName,
				fileSize, shm.shmId,
				newFileName
			);

			//Concatenando el comando a la bitacora
			sprintf (
				temporalLogContent, " %s,", nextFilterCommand
			);

			strcat ( logContent, temporalLogContent );
			//printf("%s\n", nextFilterCommand);

			gettimeofday(&inicioSegmentador, NULL);
			nextFilterResult = system(nextFilterCommand);
			gettimeofday(&finSegmentador, NULL);


			//Preparando el SMS para borrarlo
			clean_segment(
				sharedMemoryContent,
				shm.shmId
			);

		}

		printf("./ALInputFS '%s' %i %s/'%s'\n", newFileName, segmentNumber, "/ruta/Salida/", newFileName);

		milliseconds = getMilliseconds(inicioSegmentador, finSegmentador);
		//Concatenando el comando a la bitacora
		sprintf (
			temporalLogContent, " %i, ", milliseconds
		);
		strcat ( logContent, temporalLogContent );

		if(nextFilterResult > 0){
			nextFilterResult = 500;
		} else {
			nextFilterResult = 100;
		}

		gettimeofday(&finSegmentador, NULL);

		//Preparar el segmento de MC usado para su eliminacion
		//clean_segment(punteroLocalShm, idMemoriaCompartida);


	} else {
		printf( "The file %s doesn't exist\n", filePath );
	}

	/** Termina la medicion del tiempo de servicio del filtro**/
	//Calculando el tiempo de servicio
	gettimeofday(&finMetatuberia, NULL);
	milliseconds = getMilliseconds(inicioMetatuberia, finMetatuberia);


	//printf(" TrFinal: %i\n", milliseconds);
	//Adicionando el tiempo de servicio a la linea de la bitacora
	sprintf(temporalLogContent, "%i\n", milliseconds);
	strcat(logContent, temporalLogContent);

	//Comprobando el estado de apertura de la bitacora
	if(AccessLayerLog.status == 1){ 	//Esta abierta
		//Se escribe el registro a la bitacora
		writeLine(AccessLayerLog, logContent);
		closeLog(AccessLayerLog);
	} else { //No se encuentra abierta
		//Se muestra el contenido en la pantalla
		printf("Linea de la bitacora:\n%s",logContent);
	}


	return 0;

}

