#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <malloc.h>  
#include <sys/types.h>
#include <sys/time.h> //gettimeofday
#include <sys/ipc.h>
#include <sys/shm.h>
#include <sys/stat.h>
#include <unistd.h>
#include "Libraries/AccessLayer.h" // For read files and SHM
#include "Libraries/LogAdministrator.h"


//Variables Globales
unsigned int                   orden;
unsigned int                expFuncion[255];
unsigned int                logFuncion[255];
unsigned int                 a[3][3];
unsigned int                 g[3][3];
unsigned int                    d[3];
const int PRIMITIVO             =369;


//@ recorre 'v' a la derecha y determina la ultima vez que el LSb es 1 o 0.
//  -----------------------------------------------------------------------
int grado(int v)
{  int i, j;
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
//  -----------------------------------------------------------------------
unsigned int suma(unsigned int a, unsigned int b)
{
//   cout << " suma " << a << "+" <<  b;
   return(a^b);
}

//@ multiplicacion por medio de logaritmos
//  -----------------------------------------------------------------------
unsigned int mult(unsigned int a, unsigned int b)
{
//   cout << " mult " << a << "x" << b;
   if ((a!=0)&&(b!=0))
      return(expFuncion[(logFuncion[a]+logFuncion[b])%orden]);
   else
      return(0);
}

//@ division por medio de logaritmos
//  -----------------------------------------------------------------------
unsigned int division(unsigned int a, unsigned int b)
{  if (b==0)
      return(orden+1);
   else
   if (a==0)
      return(0);
   else
   return(expFuncion[(logFuncion[a]+orden-logFuncion[b])%orden]);
}


//@ invierte una matriz cuadrada de 3x3
//  -----------------------------------------------------------------------
void invierteM()
{
   int i,j;
   unsigned int d;

   d=0;
   for (j=0; j<3; j++)
      d=suma(d,mult(a[0][j],
                    suma(mult(a[1][(j+1)%3],a[2][(j+2)%3]),
                         mult(a[1][(j+2)%3],a[2][(j+1)%3]))));

//   cout << "d = " << d << endl;

   for (i=0; i<3; i++)
      for (j=0; j<3; j++)
         g[j][i]= division(
			suma(mult(a[(i+1)%3][(j+1)%3],a[(i+2)%3][(j+2)%3]),
            mult(a[(i+1)%3][(j+2)%3],a[(i+2)%3][(j+1)%3])), d
         );
}


//@ genera el campo finito a partir de su polinomio primitivo.
//  -----------------------------------------------------------------------
void generaGF()
{  unsigned int      dg;        // cociente parcial ("toca a 0 o 1?")
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
   for (i=0; i<orden; i++)
   {  leng2 = grado(rx);
      dg    = 1;

      dg = (leng2-leng1>=0)?     dg<<(leng2-1): 0;
      ngx= (leng2-leng1>=0)? gx<<(leng2-leng1): 0;

      while (ngx >= gx)
      {  if (dg==(rx&dg))       // si "toca" a 1
            rx=rx^ngx;
         dg=(dg>>1);
         ngx=(ngx>>1);
      };

      expFuncion[i]=rx;
//      cout << "expFuncion[" <<i << "]=" << rx<< ",\n";
      logFuncion[rx]=i;
      rx=rx<<1;
   };
}


char *realizarRecuperacion(int tamanioFinal, int sizeFiles, char *contenido1, char *contenido2, char *contenido3){
	
	char c, b[3], c3[12]={0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0};
	int i, j, r, primera;
	unsigned int t, *aux[3];

	
	int contadorArchivo1 = 0;
	int contadorArchivo2 = 0;
	int contadorArchivo3 = 0;
	int contadorSalida = 0;
	
	char *salidaFinal;
	salidaFinal = (char*) malloc(sizeof(char)*(tamanioFinal));
	for (i=0; i<3; i++){
      switch (i)
      {  case 0:  
			r = contenido1[contadorArchivo1];
			contadorArchivo1++;
            for (j=0; j<3; j++) {
				a[i][j] = contenido1[contadorArchivo1];
				contadorArchivo1++;
			}
            break;
         case 1:  
			r = contenido2[contadorArchivo2];
			contadorArchivo2++;
            for (j=0; j<3; j++) {
				a[i][j] = contenido2[contadorArchivo2];
				contadorArchivo2++;
			}
            break;
         case 2:  
			r = contenido3[contadorArchivo3];
			contadorArchivo3++;
            for (j=0; j<3; j++) {
				a[i][j] = contenido3[contadorArchivo3];
				contadorArchivo3++;
			}
            break;
      };
	}

	invierteM();
	primera=1;
	for (j=0; j<3; j++) {
		aux[j] = (unsigned int *) &c3[4*j];
	}

	do {  
	   for (j=0; j<3; j++) {
			 switch (j) {  
				case 0:  
					c = contenido1[contadorArchivo1];
					contadorArchivo1++;
					if (contenido1 != '\0') {  
						c3[4*j]=c;
						d[j]=(*aux[j]);
					};
					break;
				case 1:  
					c = contenido2[contadorArchivo2];
					contadorArchivo2++;
					if (contenido2 != '\0') {  
						c3[4*j]=c;
						d[j]=(*aux[j]);
					};
					break;
				case 2:  
					c = contenido3[contadorArchivo3];
					contadorArchivo3++;
					if (contenido3 != '\0') {  
						c3[4*j]=c;
						d[j]=(*aux[j]);
					};
					break;
			 };
		}

		if ((contenido1 != '\0')&&(!primera)) {  
			for (i=r; i<3; i++) {
				salidaFinal[contadorSalida] = b[i];
				contadorSalida++;
			}
		}

		if (contenido1 != '\0')
		{  
			for (i=0; i<3; i++) {  
				t=0;
				for (j=0; j<3; j++)
					t=suma(t, mult(g[i][j], d[j]));
					b[i]=t;
			}

			for (i=0; i<r; i++) {
				salidaFinal[contadorSalida] = b[i];
				contadorSalida++;
			}
      }

		if ((contenido1 == '\0')&&(r==0)){
			for (i=r; i<3; i++){          
				salidaFinal[contadorSalida] = b[i];
				contadorSalida++;
			}
		}
		primera=0;
	}
	while (contadorArchivo1 < sizeFiles);
	return salidaFinal;
}



/**
 * Recupera los chunks de los segmentos solicitados y los reconstruye
 * */
int main (int argc, char ** argv){
	
	//Variables para la bitacora
	struct timeval inicioReconstruir, finReconstruir;
	struct timeval inicioObtenerChunks, finObtenerChunks;
	struct timeval inicioIda, finIda;
	struct LogFile IDALogFile;
	char *logPath = "Logs/PL_IDADecode_1FS2R.csv";
	char logContent[255];
	char temporalLogContent[50];
	int metadataSize = 500;//Tamanio de la Metadata Cr1
	
	
	gettimeofday(&inicioReconstruir, NULL);
	strcpy (temporalLogContent, "");
	strcpy (logContent, "");
	int milisegundos;
	
	if(!fileExists(logPath)){
		sprintf(
			logContent, "NombreDelArchivo, TDescarga3chunks, TrIda, Tamanio, TrLocal \n"
		);
	}
	
	//Nombre del siguiente Filtro
	int shm_id = atoi(argv[1]);
	char *nombreArchivo = argv[2];//"20170331_152413_677387_F3_100MB.txt";

	
	
	//Se abre el archivo
	IDALogFile = openLog( logPath );
	if ( IDALogFile.status == 1 ){
		writeLine(IDALogFile, logContent);
		sprintf(logContent, "%s", "");
	} else {
		printf("\t\tSalidaMC2R Error: No se pudo abrir la bitacora\n Favor ");
		printf("de verificar que la ruta %s exista", 
				logPath);
	}
	
	sprintf(logContent, "'%s', ", nombreArchivo);
	
	
	int resultadoSiguienteFiltro = 0;	
	
	char comando[300]; //comando para invocar al siguiente filtro	
	int segmentosRE = 0; //Segmentos recuperados exitosamente
	
	//Variables MC metadata para Filtro anterior
	char mensajeCompartido[255];
	strcpy(mensajeCompartido, "");
	
	gettimeofday(&inicioObtenerChunks, NULL);	
	
	//SHMSegments metadata
	struct shmSegment metadata0;
	struct shmSegment metadata1;
	struct shmSegment metadata2;

	//Metadata contents
	char *metadataContent0;
	char *metadataContent1;
	char *metadataContent2;
	
	//Chunks content
	char *contentChunk0;
	char *contentChunk1;
	char *contentChunk2;

	//Abriendo los segmentos
	//printf("-----IDA generando los SHMSegments\n");		
	metadata0 = generateShmKeyAndOpenShmSegment(metadataSize);
	metadata1 = generateShmKeyAndOpenShmSegment(metadataSize);
	metadata2 = generateShmKeyAndOpenShmSegment(metadataSize);
	//printf("-----IDA Finalizó la generación los SHMSegments\n");		
	
	int esPosibleRecuperarElArchivo = 0;

	
	//El primer chunk es extraido del almacenamiento local
	//Concatenando el comando para ejecutar la peticion del Cr1		
	char nombreC1[150];
	strcpy(nombreC1, "");
	sprintf(nombreC1, "'c1_%s'", nombreArchivo);


	sprintf ( 
		comando, "./%s %i 'chunk1/c1_%s' ", "ALGetContentFS", 
		metadata0.shmId, nombreArchivo
	);

	//printf("\t\tComando %s\n", comando);
	
	//Ejecutar el siguiente filtro	
	printf("%s, ", nombreC1);			
	resultadoSiguienteFiltro = system ( comando );
	
	//Se evalua el resultado
	if(resultadoSiguienteFiltro > 0){	
		printf("\tReconstruir No existe el archivo Localmente 'c1_%s' \n", nombreArchivo);
	} else { 
		resultadoSiguienteFiltro = 100;
		segmentosRE++;
	}	
	
	/** 
	 * Se recuperaran los siguientes dos chunks desde los 4 server
	 * disponibles en el archivo de configursacion
	 **/ 
	for (int i = 0; i < 4; i++){
		
		//printf("\tReconstruir Recuperando Chunk %i \n", i+1);
		resultadoSiguienteFiltro = 0;
		
		if(segmentosRE == 3){ 

			printf("\n");
			printf("-----IDA The three chunks were downloaded correctly,");
			printf(" The decoding process starts\n");
			break;

		} else if(i == 2 && segmentosRE == 0) {
			printf("\t-----IDA  The necessary chunks can not be recovered\n");
			esPosibleRecuperarElArchivo = -1;
			break;
		}
		
		/**
		 * Se realiza la descarga de los chunks 2 y tres. Al ser 
		 * descargado el segundo es almacenado en su SMS y se pasa a 
		 * descargar el tercero y guardarlos en si SMS.
		 **/
		int metadataActual = 0;
		switch (segmentosRE) {
			case 0:
				metadataActual = metadata0.shmId;
			break;
			case 1:
				metadataActual = metadata1.shmId;
			break;
			case 2:
				metadataActual = metadata2.shmId;
			break;
		}

		sprintf ( 
			comando, "./%s %i 'chunk%i/c%i_%s' ", "ALGetContentFS", 
			metadataActual, i+2, i+2 ,nombreArchivo
		);

		//sprintf ( 
		//	comando, "./%s %i %i c%i_%s %i %s 0", nombreSiguienteFiltro1, 
		//	0, metadataActual, i+2, nombreArchivo, sDisponibles[i].puerto,  sDisponibles[i].direccionIp
		//);

		printf("'c%i_%s', ", i+2, nombreArchivo);
				
		//Ejecutar el siguiente filtro		
		resultadoSiguienteFiltro = system ( comando );
		
		//Se evalua el resultado
		if(resultadoSiguienteFiltro > 0){	
			printf("\tNo se pudo descargar el chunk 'c%i_%s' \n", i+2, nombreArchivo);
		} else { 
			resultadoSiguienteFiltro = 100;
			segmentosRE++;
		}		

		
	}

	
	if(esPosibleRecuperarElArchivo == 0) {	
	
		//Obteniendo obteniendo el contenido de los SHMSegments
		//printf("-----IDA Enlazando las variables locales (metadata) con los SHMSegments\n");		
		metadataContent0 = attach_segment(metadata0.shmId);
		metadataContent1 = attach_segment(metadata1.shmId);
		metadataContent2 = attach_segment(metadata2.shmId);
			
		//Estructuras para las metadatas
		struct contenidoMsjMetadata contenidoMsj;
		struct contenidoMsjMetadata contenidoMsj2;
		struct contenidoMsjMetadata contenidoMsj3;
		
		//Se separa el contenido de los SHMSegments de metadata
		//printf("-----IDA Recuperando el contenido de la Metadata\n");		
		contenidoMsj = recuperarElementosMsj(metadataContent0);
		contenidoMsj2 = recuperarElementosMsj(metadataContent1);
		contenidoMsj3 = recuperarElementosMsj(metadataContent2);

		//Se lee el contenido de lo que esta dentro de los SHMSegments
		//printf("-----IDA Enlazando las variables locales (data) con los SHMSegments\n");		
		contentChunk0 = attach_segment(contenidoMsj.keyMC);
		contentChunk1 = attach_segment(contenidoMsj2.keyMC);
		contentChunk2 = attach_segment(contenidoMsj3.keyMC);

		
		gettimeofday(&finObtenerChunks, NULL);	
		
		milisegundos = (
			(finObtenerChunks.tv_usec - inicioObtenerChunks.tv_usec) + 
			((finObtenerChunks.tv_sec - inicioObtenerChunks.tv_sec) * 
			1000000.0f)
		)/1000;
		
		sprintf(logContent, "%s%i, ", logContent, milisegundos);
		gettimeofday(&inicioIda, NULL);
		
		char miRutaFinal [255] ;
		strcpy (miRutaFinal, "");
		
		long tamanioFinal = 0;
		int bytesAgregados = 15;
		int residuoDelOriginal = 0;
		int bytesSobrantes = 0;
		char *salidaFinal;
		
		//Se obtiene el residuo del archivo
		residuoDelOriginal = contentChunk0[0];
		//printf("Residuo: %i\n", residuoDelOriginal);
		bytesSobrantes = bytesAgregados - residuoDelOriginal;
		//printf("Sobrantes: %i\n", bytesSobrantes);
		tamanioFinal = contenidoMsj.tamanioDelArchivo * 3 - bytesSobrantes;
		
		//salidaFinal = (char*) malloc(sizeof(char)*(tamanioFinal));
		
		//Metadata que será enviada al filtro anterior
		char *metadataPreviousFilter;
		metadataPreviousFilter = attach_segment(shm_id);
		
		//printf("-----IDA generando el SHMSegment de la Salida\n");		
		//Contenido decodificado que será pasado al filtro anterior
		struct shmSegment structDecodedContent;
		structDecodedContent = generateShmKeyAndOpenShmSegment(tamanioFinal);
		char *dataPreviousFilter;
		dataPreviousFilter = attach_segment(structDecodedContent.shmId);
		//printf("-----IDA Finalizó la generación del SHMSegment de la salida\n");		
		

		generaGF();
		salidaFinal = realizarRecuperacion(
			tamanioFinal, contenidoMsj.tamanioDelArchivo, 
			contentChunk0, contentChunk1, contentChunk2
		);

		
		gettimeofday(&finIda, NULL);
		
		milisegundos = (
			(finIda.tv_usec - inicioIda.tv_usec) + 
			((finIda.tv_sec - inicioIda.tv_sec) * 
			1000000.0f)
		)/1000;
		
		sprintf(logContent, "%s%i, ", logContent, milisegundos);

		//Colocando datos en la mc de mensajes
		sprintf(
			mensajeCompartido, "%ld %i", tamanioFinal, structDecodedContent.shmId
		);
		
		//Colocando la metadata en el SHMSegment
		copyFromLocalToShm(
			metadataPreviousFilter, mensajeCompartido, metadataSize
		);
		
		////Colocando el contenido procesado en el SHMSegment
		copyFromLocalToShm(
			dataPreviousFilter, salidaFinal, tamanioFinal
		);

		//sprintf(nombreC1, "Pruebas/%s", nombreArchivo);
		//writeFile(dataPreviousFilter, tamanioFinal, nombreC1);
		
		//Borrando el contenido que ya se coloco en la SHMSegment
		free(salidaFinal);

		//Borrando los SHMSegments de la metadata de los chunks
		clean_segment(metadataContent0,metadata0.shmId);
		clean_segment(metadataContent1,metadata1.shmId);
		clean_segment(metadataContent2,metadata2.shmId);

		//Borrando los SHMSegments de los chunks descargados
		clean_segment(contentChunk0, contenidoMsj.keyMC);
		clean_segment(contentChunk1, contenidoMsj2.keyMC);
		clean_segment(contentChunk2, contenidoMsj3.keyMC);

		gettimeofday(&finReconstruir, NULL);
		
		milisegundos = (
			(finReconstruir.tv_usec - inicioReconstruir.tv_usec) + 
			((finReconstruir.tv_sec - inicioReconstruir.tv_sec) * 
			1000000.0f)
		)/1000;
		
		sprintf(logContent, "%s%i\n", logContent, milisegundos);
		
		if(IDALogFile.status == 1){ 	
			writeLine(IDALogFile, logContent);					
			closeLog(IDALogFile);
		} else {
			printf("Linea de la bitacora:\n%s",logContent);
		}
		
		return 0;
		
		
	} else {
		printf("-----IDA NO es posible generar el archivo\n");
		printf("-----No se pudieron recuperar 3 de los 5 segmentos.\n");
		return 500;
	}
}
