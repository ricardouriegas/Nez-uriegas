#include <stdio.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/time.h> 
#include <sys/stat.h>
#include <netinet/in.h>
#include <stdlib.h>
#include <string.h>
#include <netdb.h>        
#include <unistd.h>
#include "Libraries/AccessLayer.h" // For read files and SHM
#include "Libraries/LogAdministrator.h"

//Variables used for the ShmS of the previous filter
int id_shm;
char *shm_localPointer;
long fileSize;
int portNumber;
char *ipAddress;
char *fileName;
int serverRequest;
char *cwd;


char *shm_localPointer;
int metadataSize = 500;



int main ( int argc, char *argv[]) {
	
	//Tiempo de servicio
	struct timeval timerResponseTimeInit, timerResponseTimeEnd;
	//Tiempo de comunicacion para el envio de los datos
	struct timeval timerTransportInit, timerTransportEnd;
	int milisegundos = 0;
	
	gettimeofday(&timerResponseTimeInit, NULL);
	
	//Para la bitacora
	struct LogFile outputNetworkLog;
	char *pathLog = "Logs/AL_OutputN.csv";
	char logContent[255];
	char temporalLogContent[50];
	cwd = getCurrentWorkDirectry();	
	strcpy (temporalLogContent, "");
	strcpy (logContent, "");
	
	if(!fileExists(pathLog)){
		sprintf(logContent, "Petition, fileName, fileSize, Port, SRValue, Sent/ReceiveTime, Tr \n");
	}
	
	//Se abre el archivo
	outputNetworkLog = openLog( pathLog );
	if ( outputNetworkLog.status == 1 ){
		writeLine(outputNetworkLog, logContent);
	} else {
		printf("\t\tALOutputN Error: No se pudo abrir la bitacora\n Favor ");
		printf("de verificar que la ruta %s exista", 
				pathLog);
	}
	

	fileSize = atol (argv[1]);
	id_shm = atoi(argv[2]);
	fileName = argv[3];

    //Definiciones obligatorias para red
    int sockfd, n;
    struct sockaddr_in serv_addr;
    struct hostent *server;
    char buffer[256];    
    //Puerto de escucha del servidor
    portNumber = atoi(argv[4]);
	ipAddress = argv[5];
	serverRequest = atoi(argv[6]);
	
	char *realServerRequest;

	switch(serverRequest){
		case 0:
			realServerRequest = "GET";
		break;
		case 1:
			realServerRequest = "PUT";
		break;
	}

	sprintf ( 
		temporalLogContent, "%s, %s, %ld, %i, %i,  ", realServerRequest, fileName, 
		fileSize, portNumber, serverRequest
	);
	
	strcat ( logContent, temporalLogContent );
    
    //******************************************************************
    //------------------------Inicia paso 02----------------------------
    //****************************************************************** 
    /**
     * Iniciando el paso 2:
     * Establecer conexión con el servidor usando su ip 
     * y puerto habilitado
     **/
    
	sockfd = obtenerSocketFileDescriptor();
	if (sockfd < 0) { error("\t\tALOutputN ERROR opening socket"); }
		
	server = gethostbyname(ipAddress);
	
	if (server == NULL) {
		fprintf(stderr,"\t\tALOutputN ERROR, no such host\n");
		exit(0);
	}

	bzero((char *) &serv_addr, sizeof(serv_addr));
	serv_addr.sin_family = AF_INET;
	bcopy((char *)server->h_addr, 
	      (char *)&serv_addr.sin_addr.s_addr, 
	      server->h_length);
	serv_addr.sin_port = htons(portNumber);
	
	if (conectarConServidor(sockfd,
			   (struct sockaddr *)&serv_addr,
			   sizeof(serv_addr)) < 0){ 
		error("\t\tALOutputN ERROR connecting");
	}
    //==================================================================
    //-------------------------Fin del paso 02--------------------------
    //==================================================================
        
    
    
    //******************************************************************
    //------------------------Inicia paso 03----------------------------
    //******************************************************************  
    /**
     * Iniciando el paso 3:
     * 3.- Escribir el mensaje en formato "tamaño nombre"
     **/
		
	strcpy(buffer, "");
	//printf("%i\n", serverRequest);
	
	sprintf (
		buffer, "%ld %s %i", fileSize, fileName, serverRequest
	);
			
	n = send(sockfd,buffer,strlen(buffer), 0);
	
	if (n < 0) { error("\t\tALOutputN ERROR writing to socket"); }
    //==================================================================
    //-------------------------Fin del paso 03--------------------------
    //==================================================================
    

    switch(serverRequest){
		case 0: //GET
			//******************************************************************
			//------------------------Inicia paso 07----------------------------
			//****************************************************************** 
			/**
			 * Iniciando el paso 7:
			 * 3.- Leer respuesta del cliente
			 **/
			bzero(buffer,256);
			n = recv(sockfd, buffer,3,0);
			if (n < 0) { error("\t\tALOutputN ERROR reading from socket"); }

			int serverResponse = atoi(buffer);

			if(serverResponse == 100){
				//Recibiendo el tamaño del archivo
				bzero(buffer,256);
				n = recv(sockfd, buffer,255,0);
				if (n < 0) { error("\t\tALOutputN ERROR reading from socket"); }

				//Leyendo el tamaño del archivo
				gettimeofday(&timerTransportInit, NULL);
					fileSize = atol(buffer);
					char * readedContent;
					//Reservando el espacio en memoria para alojar el contenido por enviar
					readedContent = (char*)malloc(sizeof(char)*fileSize);
					//Se lee el contenido
					size_t na; //Bytes leidos
					na = readn ( 
						sockfd, readedContent, fileSize
					);
					if (na < 0) { error("\t\tALOutputN ERROR reading from socket"); }
				gettimeofday(&timerTransportEnd, NULL);

				milisegundos = getMilliseconds(timerTransportInit, timerTransportEnd);
				//Tiempo de envio de los datos
				sprintf( temporalLogContent, "%i, ", milisegundos );
				strcat( logContent, temporalLogContent );

				//Se genera el SHMSegment para la metadata del siguiente filtro
				char *metadataPreviousFilter = attach_segment(id_shm);

				//Se genera el SHMSegment para el contenido que se pasará al filtro anterior
				struct shmSegment structSharedContent;
				structSharedContent = generateShmKeyAndOpenShmSegment(fileSize);
				char *sharedContent;
				sharedContent = attach_segment(structSharedContent.shmId);

				//
				char mensajeCompartido[255];
				strcpy(mensajeCompartido, "");

				sprintf(
					mensajeCompartido, "%ld %i", fileSize, structSharedContent.shmId
				);

				//Colocando los datos leidos en el SHMSegment
				copyFromLocalToShm(
					sharedContent, readedContent, fileSize
				);
				
				//char fullName[255];
				//strcpy(fullName, "");
				//sprintf(fullName, "Results/%s", fileName);
				//writeFile(sharedContent, fileSize, fullName);

				//Colocando la metadata en el SHMSegment
				copyFromLocalToShm (
					metadataPreviousFilter, mensajeCompartido, metadataSize
				);	

				//Se libera el espacio leido
				free(readedContent);

				//Enviando respuesta al servidor
				n = send(sockfd,"100",strlen(buffer), 0);
				if (n < 0) { error("\t\tALOutputN ERROR writing to socket"); }
				gettimeofday(&timerResponseTimeEnd, NULL);
				milisegundos = getMilliseconds(timerResponseTimeInit, timerResponseTimeEnd);		
				sprintf(temporalLogContent, "%i \n", milisegundos);
				strcat(logContent, temporalLogContent);				
				if(outputNetworkLog.status == 1){ 	
					writeLine(outputNetworkLog, logContent);					
					closeLog(outputNetworkLog);
				} else {
					printf("Linea de la bitacora:\n%s",logContent);
				}									
				close(sockfd);
				return 0;
			} else {
				//printf("\t\tALOutputN error Cliente \n");
				if(outputNetworkLog.status == 1){ 	
					writeLine(outputNetworkLog, logContent);					
					closeLog(outputNetworkLog);
				} else {
					printf("Linea de la bitacora:\n%s",logContent);
				}
				close(sockfd);
				return -1;
			}		
		//==================================================================
		//-------------------------Fin del paso 07--------------------------
		//==================================================================
		
		break;
		case 1: //PUT
			//******************************************************************
			//------------------------Inicia paso 07----------------------------
			//****************************************************************** 
			/**
			 * Iniciando el paso 7:
		 	* 3.- Leer respuesta del cliente
		 	**/
			bzero(buffer,256);
			n = recv(sockfd, buffer,255,0);
			if (n < 0) { error("\tERROR reading from socket"); }
			
			//==================================================================
			//-------------------------Fin del paso 07--------------------------
			//==================================================================
		
		
			//******************************************************************
			//------------------------Inicia paso 08----------------------------
			//******************************************************************
			/**
			 * Iniciando el paso 8:
			 * Escribir contenido correspondiente a los datos anteriores
			 **/
		 
		 
		

		shm_localPointer = attach_segment(id_shm);
			
		gettimeofday(&timerTransportInit, NULL);
		
		size_t na;	
		
		na = writen(
			sockfd, shm_localPointer,	fileSize
		);
		
		clean_segment(
			shm_localPointer, 
			id_shm
		);	
		
			
		if (na < 0) { error("\tERROR writing to socket"); }
		
		gettimeofday(&timerTransportEnd, NULL);
		milisegundos = getMilliseconds(timerTransportInit, timerTransportEnd);
		//Tiempo de envio de los datos
		sprintf( temporalLogContent, "%i, ", milisegundos);
		strcat( logContent, temporalLogContent );
			
		//==================================================================
		//-------------------------Fin del paso 08--------------------------
		//==================================================================
		
		
		
		//******************************************************************
		//------------------------Inicia paso 12----------------------------
		//****************************************************************** 
		/**
		 * Iniciando el paso 12:
		 * Leer respuesta del servidor
		 **/
			bzero(buffer,256);	
			n = recv(sockfd, buffer,3,0);
			if (n < 0) { error("PASO 12: ERROR reading from socket"); }
		//==================================================================
		//-------------------------Fin del paso 12--------------------------
		//==================================================================    
		
		//******************************************************************
		//------------------------Inicia paso 13----------------------------
		//****************************************************************** 
		/**
		 * Iniciando el paso 13:
		 * Cerrando la conecion con el servidor
		 **/
			close(sockfd);
			//printf("   SalidaMC2R Socket del cliente cerrado\n");
		//==================================================================
		//-------------------------Fin del paso 13--------------------------
		//==================================================================
			
		gettimeofday(&timerResponseTimeEnd, NULL);
		milisegundos = getMilliseconds(timerResponseTimeInit, timerResponseTimeEnd);
		sprintf(temporalLogContent, "%i \n", milisegundos);
		strcat(logContent, temporalLogContent);
			
		if(outputNetworkLog.status == 1){ 	
			writeLine(outputNetworkLog, logContent);					
			closeLog(outputNetworkLog);
		} else {
			printf("Linea de la bitacora:\n%s",logContent);
		}

		break;
		default:
		printf("Opcion no disponible");
		break;
	}
    
    return 1;
}