//Funciona dentro y fuera del contenedor
#include <stdio.h>
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/shm.h>
#include <string.h>
#include <sys/time.h> //gettimeofday
#include <unistd.h> //getpid
#include "Libraries/AccessLayer.h" 


//Variables used for the ShmS of the previous filter
int id_shm; //shm id of the ShmS
long fileSize; //Size of the ShmS
char *shm_localPointer; //Recovered data of the ShmS

//id_shm, nombreArchivo
int main(int argc, char **argv){
	    
    /*-----------------Memoria----------------------*/
    //Definiciones obligatorias para la memoria compartida	
	char myFinalPath [255] ;
	strcpy(myFinalPath, "/home/Volume/Results/"); //Name of the finalpath
	id_shm = atoi(argv[1]);
	char *fileContent;

	char sharedMsj[255];
	
	
	//Path local
	strcat(myFinalPath, argv[2]);
	
	//Verificando que el archivo exista
	if(fileExists(myFinalPath)){		
		
		//Se obtiene el tamaño del archivo
		fileSize = getFileSize(myFinalPath);

		//Se realiza la lectura del archivo
		fileContent = readFile(myFinalPath);

		//Se genera un segmento de memoria compartida para 
		//el archivo solicitado
		struct shmSegment shm;
		shm = generateShmKeyAndOpenShmSegment(fileSize);

		//Se enlaza el puntero a una variable local
		char *shmContent = attach_segment(shm.shmId);
		
		//Colocando el contenido en el segmento de MC
		copyFromLocalToShm(shmContent, fileContent, fileSize);

		//Se libera el espacio del contenido leido local
		free(fileContent);

		
		
		//Colocando datos en la mc de mensajes
		sprintf(
			sharedMsj, "%ld %i", fileSize, shm.shmId
		);
		
		//Enlazando el shms que contiene el metadato
		shm_localPointer = attach_segment(id_shm);
		
		//
		copyFromLocalToShm(
			shm_localPointer, sharedMsj, 400
		);
		
		return 0;
		
	} else {
		printf("The file doesn't exists\n");
		return 500;
	}
}

