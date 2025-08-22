#include <netinet/in.h>  
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/shm.h>
#include <sys/stat.h>
#include <sys/socket.h>
#include "unp/lib/unp.h"
#include "Structures/File.h"
#include "Structures/ShmSegment.h"
#include "Structures/Server.h"


/**
 * Structures
 */

struct contenidoMsjMetadata {
	long tamanioDelArchivo;
	int keyMC;
};


/** 
  #####                  
 #     #  #    #  #    # 
 #        #    #  ##  ## 
  #####   ######  # ## # 
       #  #    #  #    # 
 #     #  #    #  #    # 
  #####   #    #  #    # 
**/


/**Implementations Shared Memory**/

/**
 * opens a shared memory segment and returns a valid segment id
 * */
int open_segment( key_t keyval, long segsize );

/**
 * Attach a shared memory segment id to be able to use it
 * */
char *attach_segment( int shmId );

/**
 * opens a shared memory segment and returns a valid segment id
 * */
struct shmSegment generateShmKeyAndOpenShmSegment(long segmentSize);

char * recoverDataFromShmSegment(key_t shmKey, long segmentSize);


/**
 * Changes the state of a shared memory segment to be deleted
 * */
void clean_segment(char* shmsegment, int shmid );


/**
 * Copies the contents of an array in memory to the 
 * shared memory segment
 * */
void copyFromLocalToShm(char * shmSegment, char * dataContent, long dataContentSize);


/**
 * @brief Split a message and obtain the metadata of a shared memory segment 
 * @details 
 * 
 * @param mensaje [description]
 * @return [description]
 */
struct contenidoMsjMetadata recuperarElementosMsj(char *mensaje);

/**
 * @brief Split a message and obtain the metadata of a shared memory segment 
 * @details 
 * 
 * @param mensaje [description]
 * @return [description]
 */
struct contenidoMsjMetadata recoverMessageElements(char *mensaje);

/**
  #######                          #####                                        
 #        #  #       ######      #     #  #   #   ####   #####  ######  #    # 
 #        #  #       #           #         # #   #         #    #       ##  ## 
 #####    #  #       #####        #####     #     ####     #    #####   # ## # 
 #        #  #       #                 #    #         #    #    #       #    # 
 #        #  #       #           #     #    #    #    #    #    #       #    # 
 #        #  ######  ######       #####     #     ####     #    ######  #    # 
 **/

/**
 * @brief get the current work directory
 * @details get the current work directory of the
 * application that invoces
 * @return cwd
 */
char *getCurrentWorkDirectry();

/**
 * Writes the contents of a pointer to memory within a file in the 
 * file system.
 * @param char * pointer dataContent, pointer to memory of the data 
 * you want to write
 * @param long contentSize, is the size of the bytes content 
 * you want to write 
 * @param char * outputFilePath, is the fileName of the output file
 * @return 1 if the dataContent was written correctly, 0 otherwise
 */
int writeFile( 	char * dataContent, long contentSize, char * outputFilePath);

/**
 * Check if a file exists
 * @param string fileName: path of the file to be verified on the local 
 * file system
 * @return boolean response: true if the file exists, false otherwise
 **/
int fileExists (char * fileName);

/**
 * Gets the size of a file
  * @param string pathFile, contains the path of the file 
  * on the local file system
  * @return long st_size that contains the size in bytes 
  * of the file if read correctly, 0 otherwise
 **/
long getFileSize ( char *pathFile );
/**
  * Returns the pointer to memory of the contents of the file read
  * in bytes format
  * @param pathFile, the path of the file in the local file system
  * @return pointer bufferFileInput (char *), the pointer 
  * to memory of the read file.
 **/
char * readFile(char * filePath);

/**
 * @brief Create a new file name with the current time
 * @details concatenates the current date and time to a file name
 * 
 * @param currentName [the name that must be concatenate]
 * @return [a new file name with the format "yyymmdd_hhmmss_curentname"]
 */
char * generateNewName(char * currentName);


/**
  * Open a file
  * @param pathFile, file path in local file system
  * @return pointer (FILE *) bufferFileInput, pointer to memory 
  * of the read file.
 **/
FILE * obtenerArchivoLegible(char * pathArchivo);

/**
  * Open a file
  * @param pathFile, file path in local file system
  * @return pointer (FILE *) bufferFileInput, pointer to memory 
  * of the read file.
 **/
FILE * getReadableFile(char * filePath);

/**
 * Close a open file
 * @param FILE *, open file
 * */
void cerrarArchivo(FILE *archivoAbiero);

/**
 * Close a Readable File
 * @param FILE *, open file
 * */
void closeReadableFile(FILE *readableFile);

/**
  * Returns the pointer to memory of the contents of a segment 
  * of the requested file
  * @param pathFile, file path in local file system
  * @param NumberSegments, number of segments in which the file will be 
  * divided
  * @param desired segment, segment you want to get from the possible 
  * segments to create. Desired segment => 1 and 
  * Desired segment <= Segments
  * @return pointer bufferFileInput (char *), memory pointer of the 
  * readed file.
 **/
struct Archivo leerSegmentoArchivo(char *pathArchivo, int numeroSegmentos, int segmentoDeseado );


int invokesALOutputFS(int shmId, long fileSize, char *fileName);

/**
 * Returns the pointer to memory of the contents of the segment of the 
 * file read through the path entered
 * @param pathFile, contains the path of the file on the local file 
 * system
 * @param numeroSegmentos amount of segments in which the file will be 
 * divided
 * @param Desired segment is the segment you want to get from the 
 * possible segments to create. Desired segment> = 1 and 
 * Desired segment <= Segments
 * @return struct Data file of the read segment 
 * (content and size in bytes)
 **/
struct Archivo segmentarContenidoArchivo ( char *bufferArchivoEntrada, long tamanioEntrada, int numeroSegmentos, int segmentoDeseado );

/**
 #     #                                                
 ##    #  ######  #####  #    #   ####   #####   #    # 
 # #   #  #         #    #    #  #    #  #    #  #   #  
 #  #  #  #####     #    #    #  #    #  #    #  ####   
 #   # #  #         #    # ## #  #    #  #####   #  #   
 #    ##  #         #    ##  ##  #    #  #   #   #   #  
 #     #  ######    #    #    #   ####   #    #  #    # 

 * **/

/**
 * Return a valid Socker File Descriptor
 * */
int obtenerSocketFileDescriptor();
/**
 * Return a valid Socker File Descriptor
 * */
int getSocketFileDescriptor();

/**
 * Bind Socket File Descriptor to a IP Server
 * */
int realizarEnlace(int fileDescriptor, const struct sockaddr *addr, socklen_t addrlen);

/**
 * Bind Socket File Descriptor to a IP Server
 * */
int makeLink(int sfd, const struct sockaddr *addr, socklen_t addrlen);

/**
 * Enable the server socket to listen for client socket requests
 * */
void empezarEscucha(int fileDescriptor, int numberOfThreads);

/**
 * Enable the server socket to listen for client socket requests
 * */
void listenPetitions(int sfd, int numberOfThreads);
/**
 * @brief Close opened socket file descriptor
 * 
 * @param sfd a opened sfd
 */
void cerrarSocketFileDescriptor(int sfd);

/**
 * @brief Send a message througth the opened socket
 * 
 * @param sfd Socket file descriptor
 * @param message message to send
 * @param messajeSize is the size of the message to send
 */
void sendMessage(int sfd, char* message, int messajeSize);

/**
 * @brief Read a message througth the opened socket
 * 
 * @param sfd Socket file descriptor
 * @param message where the message will storage
 * @param messajeSize is the size of the message to read
 */
void receiveMessage(int sfd, char* message, int messajeSize);


/**
 * @brief Sends a large amount of bytes
 * @details Sends a number of bytes greater than that allowed by a one socket file descriptor (sfd) writing
 * 
 * @param sfd: the sfd of the client socket
 * @param fileContent: Byte array that contains the data to be sent
 * @param fileSize:  The amount of bytes that must be traded
 */
void senFileContent(int sfd, char *fileContent, long fileSize);

/**
 * @brief Receives a large amount of bytes
 * @details Receives a number of bytes greater than that allowed by a one socket file descriptor (sfd) reading
 * 
 * @param sfd: the sfd of the client socket
 * @param fileContent: Byte array where the read data is stored
 * @param fileSize: The amount of bytes that must be traded
 */
void receiveFileContent(int sfd, char *fileContent, long fileSize);
/**
 * @brief Close opened socket file descriptor
 * 
 * @param sfd a opened sfd
 */
void closeSFD(int sfd);

/**
 * @brief Connect a socket client with a server socket
 * @details Stablish the conecction between a socket file descriptor (sfd) with the 
 * server socket to send and write messages
 * 
 * @param sfd the sfd of the client socket
 * @param sockaddr information about the server socket
 * @param addrlen information about the server socket
 * @return a positive number if the conection can stablish, otherwise a negative number
 */
int conectarConServidor(int sfd, const struct sockaddr *addr, socklen_t addrlen);

/**
 * @brief Connect a socket client with a server socket
 * @details Stablish the conecction between a socket file descriptor (sfd) with the 
 * server socket to send and write messages
 * 
 * @param sfd the sfd of the client socket
 * @param sockaddr information about the server socket
 * @param addrlen information about the server socket
 * @return a positive number if the conection can stablish, otherwise a negative number
 */
int conectToServer( int sfd, const struct sockaddr *addr, socklen_t addrlen);

/**
 * @brief Print message and stop the aplication
 * @details Shows an error message and stop the current application
 * 
 * @param msg The error message that the user want to show
 */
void error(char *msg);


/* Read "n" bytes from a descriptor. */
/**
 * @brief  Read "n" bytes to a socket file descriptor (sfd).
 * @details Read a sfd while the sum of the bytes readed are less than n
 * 
 * @param sfd socket file descriptor
 * @param ataContent; is where the readed bytes will be storage
 * @param n the total of bytes that must be readed
 * @return [description]
 */
ssize_t readn(int sfd, void *dataContent, size_t n);

/**
 * @brief Write "n" bytes to a socket file descriptor (sfd).
 * @details writes "n" bytes un a sfd that will send to the socket server
 * 
 * @param sfd socket file descriptor
 * @param dataContent; data that will be sent
 * @param n amount of bytes that will be sent
 * @return the number of bytes writed in the sfd
 */
ssize_t writen ( int sfd, const void *dataContent, size_t n );

/**
 * @brief Read a file with the servers information
 * @details Each line of the congiguration files indicates the data
 * connection to one server.
 * 
 * @param path: is the path in the local file system where the configuration file is located
 * @param numberOfWorkers: is the number of the servers that will be read in the file
 * @param Server: array that will storage the data of the servers
 * @return an array with the severs data
 */
int getInformationServers(char *path, int numberOfWorkers, struct Server *servers);

int invokesALOutputN(int shmId, long fileSize, char *fileName, char *serverIp, int serverPort);


//Funciones comunes
double getMilliseconds(struct timeval start,struct timeval end);
void RandTimeInit(void);
float BestRand(double);
