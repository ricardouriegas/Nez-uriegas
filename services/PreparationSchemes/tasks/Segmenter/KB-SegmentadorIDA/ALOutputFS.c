#include <stdio.h>
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/shm.h>
#include <string.h>
#include "Libraries/AccessLayer.h" 



 //Variables used for the ShmS of the previous filter
int id_shm; //shm id of the ShmS
long fileSize; //Size of the ShmS
char * shm_localPointer; //Recovered data of the ShmS

/**
 * @brief Writes a char * into the file system
 * @details Open a file Object and writes a char *
 * 
 * @param argc number of parameters
 * @param argv array with the input parameters
 * 
 * @return 1 if the filter was executed correctly, -1 otherwise
 */
int main(int argc, char **argv){
	
//Shared memory (Shm)
//Shared memory segment (ShmS)

	//Local variables   
	char myFinalPath[255]; 
	
	
	//The variables are initialized
	//strcpy(myFinalPath, "/home/hreyes/Demos/DataSink/"); //Name of the finalpath
	strcpy(myFinalPath, "/home/Volume/Results/");
	fileSize = atol(argv[1]); //Size of the ShmS
	id_shm = atoi(argv[2]);
	
	//Concatenate the file name to the final path
	strcat(myFinalPath, argv[3]);
	
	shm_localPointer = attach_segment(id_shm);
	
	if (writeFile(shm_localPointer, fileSize, myFinalPath)){
		//printf("The file %s was writed correctly\n", myFinalPath);
		//Clean de ShmS
		clean_segment(
			shm_localPointer, 
			id_shm
		);
		//printf("The filter %s was executed correctly\n", argv[0]);
		return 1;

	} else {
		//printf("The filter %s filed\n", argv[0]);
		return 0;
	}
	

}

