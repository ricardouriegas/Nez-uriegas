#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/shm.h>
#include <sys/time.h> 
#include <sys/wait.h>
#include "Libraries/AccessLayer.h" 
#include "Libraries/LogAdministrator.h"


/*-----------------Globales---------------------*/

	struct timeval inicioFiltro;
	char *cwd;
	

/*-----------------Memoria----------------------*/
    //Definiciones obligatorias para la memoria compartida	(Entrada)
	int shmIdompartidaCliente;
	int fileSize;
	int shmId;

/*-----------------Memoria----------------------*/
    //Definiciones obligatorias para la memoria compartida	(Salida)
    int shmIdompartidaSalida;
    char * punteroLocalShmSalida;
    long fileSizeSalida;
    
/*--------------Variables Globales--------------*/
	
	char * fileName;

   unsigned int orden;
   unsigned int exponente[256];
   unsigned int logaritmo[256];
   unsigned int a[5][3]={
	   { '1', '3', '2' } ,
       { '1', '1', '1' } ,
       { '2', '3', '1' } ,
       { '2', '2', '3' } ,
       { '2', '3', '3' }
   };

   unsigned int b[3];
   const int PRIMITIVO = 369;




/*--------------Definicion de Métodos--------------*/
int grado ( int v );
unsigned int suma ( unsigned int a, unsigned int b );
unsigned int mult ( unsigned int a, unsigned int b );
void generaGF ();
void dispersa (	
	char* contenidoPorDispersar, 
	char* fileName, 
	long fileSize 
);


//@ recorre 'v' a la derecha y determina 
//la ultima vez que el LSb es 1 o 0.
//  ------------------------------------------------------------------
int grado(int v) {  
	int i, j;
	unsigned int l, w;
	l = 0;
	w = 1;
	j = 8*sizeof(i);
	for (i=1; i<j; i++)
	{  l=(v&w)? i: l;
		v=(v>>1);
	};
	return l;
}

//@ suma en modulo 2 bit por bit
//  ------------------------------------------------------------------
unsigned int suma(unsigned int a, unsigned int b){
//   cout << " suma " << a << "+" <<  b;
   return(a^b);
}

//@ multiplicacion por medio de logaritmos
//  ------------------------------------------------------------------
unsigned int mult(unsigned int a, unsigned int b){
//   cout << " mult " << a << "x" << b;
   if ((a!=0)&&(b!=0))
      return(exponente[(logaritmo[a]+logaritmo[b])%orden]);
   else
      return(0);
}

//@ genera el campo finito a partir de su polinomio primitivo.
//  ------------------------------------------------------------------
void generaGF() {  
	unsigned int      dg;        // cociente parcial ("toca a 0 o 1?")
   unsigned int      rx;        // residuo
   unsigned int      gx;        // polinomio primitivo
   unsigned int     ngx;        // g(x), desplazamiento a la izq.

   int leng1        = 0;        // grado de g(x)
   int leng2        = 0;        // grado de r(x)
   int                i;

   gx=PRIMITIVO;
   leng1 = grado(gx);
   orden = 1<<(leng1-1);
   orden = orden-1;

   rx    = 1;
   for (i=0; i < orden; i++)
   {  leng2 = grado(rx);
      dg    = 1;

      dg = (leng2-leng1>=0)?     dg<<(leng2-1): 0;
      ngx= (leng2-leng1>=0)? gx<<(leng2-leng1): 0;

      while (ngx >= gx)
      {  if (dg==(rx&dg))       // si "toca" a 1
            rx=rx^ngx;
         dg=(dg>>1);
         ngx=(ngx>>1);
      }

      exponente[i]=rx;

	logaritmo[rx]=i;  
      rx=rx<<1;
   }
}



//@ produce los cinco archivos de dispersion
//------------------------------------------------------------------
void dispersa( char* contenidoPorDispersar, char* fileName, 
	long fileSize) {
	
    int milisegundos;
	struct timeval inicioServicioFiltroDatos, finServicioFiltroDatos;
	
	//Para la bitacora
	struct LogFile LogProcessingLayer;
	char *logPath = "Logs/PL_Ida_O1FS4N.csv";
	char logContent[255];
	char temporalLogContent[50];
	strcpy (temporalLogContent, "");
	strcpy(logContent, "");
	
	//Se abre el archivo
	LogProcessingLayer = openLog( logPath );
	if ( LogProcessingLayer.status == 1 ){
		//printf ( "LogFile FiltroIDA abierta correctamente\n" );
	} else {
		printf ( "Error: No se pudo abrir la bitacora\n Favor " );
		printf ( "de verificar que la ruta %s exista", logPath );
	}
	
	
	//Iniciando el tiempo de servicio del filtro de procesamiento
	gettimeofday(&inicioServicioFiltroDatos, NULL);
	struct Servidor sDisponibles[5];
		
		//Obteniendo la ip del siguiente archivo
	FILE *archivoServidores;
	char *pathArchivoServidores = "Configuracion/Servidores.txt";
	char linea[30];
	
	if(fileExists(pathArchivoServidores)){
		archivoServidores = obtenerArchivoLegible(pathArchivoServidores);	
		for (int i = 0; i < 4; i++){
			if(fgets(linea, 30, archivoServidores) != NULL){
				//printf("Linea: %s", linea);
				char * arreglo;
				arreglo = strtok(linea, " ");					
				//Se recorren los elementos de la linea separados por "_"
				if(arreglo != NULL){
					strcpy(sDisponibles[i].direccionIp, arreglo);
				} 
				arreglo = strtok (NULL, " ");
				if(arreglo != NULL){
					sDisponibles[i].puerto = atoi(arreglo);
				}
			}	
		}	
		
		cerrarArchivo(archivoServidores);
	} else {
			printf("NO existe el archivo Configuracion/Servidores.txt\n");
			printf("Es indispensable que lo cree para continuar\n");
			printf("Cada linea del archivo representa un servidor \n"); 
			printf("la ip del servidor y el puerto de escucha de estos\n");
			printf("separados por ' ' Ejemplo:192.168.0.2 55001\n");
			exit(500);
		
	}
	
	
	char *salida1, *salida2, *salida3, *salida4, *salida5;
	//char c1, c2;
	long chunkSize;
	int i, j, t, residue, posicionArchivo1,posicionArchivo2;
	int posicionArchivo3, posicionArchivo4, posicionArchivo5;
	int posicionEntrada;
	posicionEntrada = 0;
	posicionArchivo1 = 0;
	posicionArchivo2 = 0;
	posicionArchivo3 = 0;
	posicionArchivo4 = 0;
	posicionArchivo5 = 0;
	char nombre1[100];
	char nombre2[100];
	char nombre3[100];
	char nombre4[100];
	char nombre5[100];
	
	
	strcpy(nombre1, "c1_");
	strcpy(nombre2, "c2_");
	strcpy(nombre3, "c3_");
	strcpy(nombre4, "c4_");
	strcpy(nombre5, "c5_");
	
	strcat(nombre1, fileName);
	strcat(nombre2, fileName);
	strcat(nombre3, fileName);
	strcat(nombre4, fileName);
	strcat(nombre5, fileName);
		
	//Obteniendo el tamño de los chunks
	chunkSize = fileSize/3 + 5;
	
	//Obteniendo el residuo de tamaño entre m
	residue = fileSize%3;
   
	//Reservando el espacio en memoria para los 5 segmentos	
	salida1 = (char*) malloc(sizeof(char)*chunkSize);
	salida2 = (char*) malloc(sizeof(char)*chunkSize);
	salida3 = (char*) malloc(sizeof(char)*chunkSize);
	salida4 = (char*) malloc(sizeof(char)*chunkSize);
	salida5 = (char*) malloc(sizeof(char)*chunkSize);


	// Los n+1 primeros bytes del archivo son: |F|mod(n), 
	//el exceso del archivo fuente y los n bytes del renglon 
	//de la matriz que da origen al disperso
   for (int i=0; i<5; i++){
	  switch (i){
		case 0:  
			salida1[posicionArchivo1] = residue;
			posicionArchivo1++;		
			for (j=0; j<3; j++) {
				salida1[posicionArchivo1] = a[i][j];
				posicionArchivo1++;
			}
			break;
		case 1:  			
			salida2[posicionArchivo2] = residue;
			posicionArchivo2++;
			for (j=0; j<3; j++) {
				salida2[posicionArchivo2] = a[i][j];
				posicionArchivo2++;
			}
			break;
		case 2:  
			salida3[posicionArchivo3] = residue;
			posicionArchivo3++;
			for (j=0; j<3; j++) {
				salida3[posicionArchivo3] = a[i][j];
				posicionArchivo3++;
			}
			break;
		case 3:  
			salida4[posicionArchivo4] = residue;
			posicionArchivo4++;
			for (j=0; j<3; j++) {
				salida4[posicionArchivo4] = a[i][j];
				posicionArchivo4++;
			}
			break;
		case 4:  
			salida5[posicionArchivo5] = residue;
			posicionArchivo5++;
			for (j=0; j<3; j++) {
				salida5[posicionArchivo5] = a[i][j];
				posicionArchivo5++;
			}
			break;
	  };
	}
	
	int nuevoContador = 0;
	posicionEntrada = posicionArchivo1;
	int contador = 4;
	while (contador < chunkSize)
	{
		
		for (j=0; j<3; j++)
			b[j]=0;
		
		j=0;
		
		do{  
			unsigned int valor = contenidoPorDispersar[nuevoContador];
			if(valor > 255){
				b[j++] = valor - 4294967040;
			} else {				
				b[j++] = valor;
			}
			posicionEntrada++;
			nuevoContador++;

		} while (
			(contenidoPorDispersar != '\0') &&(j<3) &&
			(posicionEntrada < (fileSize + 4))
		);
	
		if (j>0){
			
			for (i=0; i<5; i++){  
				t=0;
				for (j=0; j<3; j++){       
					if (b[j] > 255 ) break;
						t=suma(t, mult(a[i][j],b[j]));
				}
			
				switch (i) {  
					case 0:  
						salida1[posicionArchivo1] = t;
						posicionArchivo1++;
						break;
					case 1:  
						salida2[posicionArchivo2] = t;
						posicionArchivo2++;
						break;
					case 2:
						salida3[posicionArchivo3] = t;
						posicionArchivo3++;
						break;
					case 3:  
						salida4[posicionArchivo4] = t;
						posicionArchivo4++;
						break;
					case 4:  
						salida5[posicionArchivo5] = t;
						posicionArchivo5++;
					break;
				}
			}
		}
	
		contador++;
	}
	
	//free(contenidoPorDispersar);
	
	gettimeofday(&finServicioFiltroDatos, NULL);
	milisegundos = getMilliseconds(
		inicioServicioFiltroDatos,
		finServicioFiltroDatos
	);	
	
	//Imprimiendo el tiempo de servicio del filtro
	printf("st_IDA:%ims, ", milisegundos);
	sprintf(temporalLogContent, "%i, ", milisegundos);
	strcat(logContent, temporalLogContent);
	
	struct timeval inicioSalidaDatos, finSalidaDatos;
	struct timeval inicioSalidaIntefazDatos, finSalidaIntefazDatos;
	
	
	gettimeofday(&inicioSalidaDatos, NULL);

		//Preparando la salida 1
		gettimeofday ( &inicioSalidaIntefazDatos, NULL );
			
			char filename1[255];
			strcpy(filename1, "");
			sprintf(filename1, "/home/Volume/chunk1/%s", nombre1);			
			writeFile(salida1, chunkSize, filename1);
			free(salida1);

		gettimeofday( &finSalidaIntefazDatos, NULL );
	
		milisegundos = getMilliseconds(
			inicioSalidaIntefazDatos,
			finSalidaIntefazDatos
		);	
		
		//Tiempo de salida 1 (SA)
		printf("s1:%i ms, ", milisegundos);
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);

		//Preparando la salida 2
		gettimeofday ( &inicioSalidaIntefazDatos, NULL );
			
			char filename2[255];
			strcpy(filename2, "");
			sprintf(filename2, "/home/Volume/chunk2/%s", nombre2);			
			writeFile(salida2, chunkSize, filename2);
			free(salida2);

		gettimeofday( &finSalidaIntefazDatos, NULL);
		
		milisegundos = getMilliseconds(
			inicioSalidaIntefazDatos,
			finSalidaIntefazDatos
		);	

		//Tiempo de salida 2 (R)
		printf("s2:%i ms, ", milisegundos);
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);
				
		//Preparando la salida 3
		gettimeofday ( &inicioSalidaIntefazDatos, NULL );
			
			char filename3[255];
			strcpy(filename3, "");
			sprintf(filename3, "/home/Volume/chunk3/%s", nombre3);			
			writeFile(salida3, chunkSize, filename3);
			free(salida3);

		gettimeofday( &finSalidaIntefazDatos, NULL);
		
		milisegundos = getMilliseconds(
			inicioSalidaIntefazDatos,
			finSalidaIntefazDatos
		);	
		//Tiempo de salida 3 (R)
		printf("s3:%i ms, ", milisegundos);
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);
			
		//Preparando la salida 4
		gettimeofday ( &inicioSalidaIntefazDatos, NULL );

			char filename4[255];
			strcpy(filename4, "");
			sprintf(filename4, "/home/Volume/chunk4/%s", nombre4);			
			writeFile(salida4, chunkSize, filename4);
			free(salida4);

		gettimeofday( &finSalidaIntefazDatos, NULL);
		
		milisegundos = getMilliseconds(
			inicioSalidaIntefazDatos,
			finSalidaIntefazDatos
		);	
		//Tiempo de salida 4 (R)
		//free(salida4);
		printf("s4:%i ms, ", milisegundos);
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);
				
		//Preparando la salida 5
		gettimeofday ( &inicioSalidaIntefazDatos, NULL );	

			char filename5[255];
			strcpy(filename5, "");
			sprintf(filename5, "/home/Volume/chunk5/%s", nombre5);			
			writeFile(salida5, chunkSize, filename5);
			free(salida5);

		gettimeofday( &finSalidaIntefazDatos, NULL);

		milisegundos = getMilliseconds(
			inicioSalidaIntefazDatos,
			finSalidaIntefazDatos
		);	
		
		//Tiempo de salida 5 (R)
		printf("s5:%i ms, ", milisegundos);
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);				
		
	gettimeofday(&finSalidaDatos, NULL);
	milisegundos = getMilliseconds(
		inicioSalidaDatos,
		finSalidaDatos
	);	
	printf("sT:%i ms, ", milisegundos);	
		sprintf(temporalLogContent, "%i, ", milisegundos);
		strcat(logContent, temporalLogContent);	
	printf("fSize:%ld MB, ", fileSize/1024/1024);
		sprintf(temporalLogContent, "%ld, ", fileSize);
		strcat(logContent, temporalLogContent);
	printf("cSize:%ld MB ", chunkSize/1024/1024);
		sprintf(temporalLogContent, "%ld \n", chunkSize);
		strcat(logContent, temporalLogContent);

	
	if(LogProcessingLayer.status == 1){ 	
		writeLine(LogProcessingLayer, logContent);					
		closeLog(LogProcessingLayer);
	} else {
		printf("Linea de la bitacora:\n%s",logContent);
	}
	
	memset(&LogProcessingLayer, 0, sizeof LogProcessingLayer);	
	//return logContent;
		
}


//@
//------------------------------------------------------------------
int main(int argc, char **argv)
{
	//For Log
	struct LogFile LogProcessingLayer;
	char *logPath = "Logs/PL_Ida_O1FS4N.csv";
	char logContent[255];
	char temporalLogContent[50];
	char headerLine[300];
	strcpy (temporalLogContent, "");
	strcpy(logContent, "");
	strcpy(headerLine, "");
	
	//Input parameters
	int shmId;
	long fileSize;
	char *fileName;

	//For SHMS
	struct shmSegment shm;
	char *sharedMemoryContent;

	
	if(!fileExists(logPath)){
		strcat(headerLine, "ServiceTime, SendTime1, SendTime2, ");
		strcat(headerLine, "SendTime3, SendTime4, SendTime5, ");
		strcat(headerLine, "TotalSendTime ,fileSize, chunkSize\n");
	}
	
	//Se abre el archivo de la bitacora
	LogProcessingLayer = openLog( logPath );
	if ( LogProcessingLayer.status == 1 ){
		writeLine(LogProcessingLayer, headerLine);
		closeLog(LogProcessingLayer);	
		//printf ( "LogFile FiltroIDA abierta correctamente\n" );
	} else {
		printf ( "Error: No se pudo abrir la bitacora\n Favor " );
		printf ( "de verificar que la ruta %s exista", logPath );
	}
	
	
    int milisegundos;
	struct timeval inicioFiltro, finFiltro;
	struct timeval inicioEntradaDatos, finEntradaDatos;
	/** Inicia Filtro **/	
	//Tiempo de Respuesta del filtro
	gettimeofday(&inicioFiltro, NULL);

		gettimeofday(&inicioEntradaDatos, NULL);
			//Leer memoria
			//Tiempo de entrada de datos
			fileSize = atol(argv[1]);
			shmId = atoi(argv[2]);
			fileName = argv[3];
			sharedMemoryContent = attach_segment(shmId);			
		gettimeofday(&finEntradaDatos, NULL);	

		
		//Ejecutar el filtro		
		generaGF();
		dispersa(sharedMemoryContent, fileName, fileSize);		
		
		//printf("   SalidaMC2SA Borrando los datos de la shm\n");
		clean_segment(
			sharedMemoryContent, shm.shmId
		);	
		//printf("   SalidaMC2SA se ejecuto correctamente\n");	
	
	gettimeofday(&finFiltro, NULL);		
	milisegundos = getMilliseconds(
		inicioFiltro,
		finFiltro
	);	

	printf("RT_IDA:%ims \n", milisegundos);


    return 0;
}


