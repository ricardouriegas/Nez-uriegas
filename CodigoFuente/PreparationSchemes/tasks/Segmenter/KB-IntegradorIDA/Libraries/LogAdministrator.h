/**
 * Contiene los elementos necesarios para la apertura, escritura y 
 * cierre del archivo que será utilizado de bitacora
 * */

#include <stdio.h>
#include "Structures/Log.h"

/*Declaracion de funciones*/
FILE *abrirB ( char * pathBitacora );
int comprobarAbertura ( FILE *bitacora );
struct Bitacora abrirBitacora ( char *rutaBitacora );
void escribirRegistro ( struct Bitacora bitacoraAbierta, 
						char *registro );
void cerrarBitacora ( struct Bitacora bitacoraAbierta );



/**
 * Crea el archivo solicitado o lo abre si este existe en su ultima 
 * posicion para poder agregar más contenido.
 * 
 * @param char *pathBitacora: Es la ruta en la cual se quiere generar/
 * 		abrir la bitacora.
 * @return File *bitacora: Si se logra abrir el archivo retorna el 
 * 		puntero al archivo abierto, un puntero con valor NULL de lo
 * 		contrario.
 * */
FILE *abrirB ( char *pathBitacora ) {
	FILE *bitacora;
	bitacora = fopen ( pathBitacora, "a" );
	return bitacora;
}

/**
 * Crea el archivo solicitado o lo abre si este existe en su ultima 
 * posicion para poder agregar más contenido.
 * 
 * @param char *pathBitacora: Es la ruta en la cual se quiere generar/
 * 		abrir la bitacora.
 * @return File *bitacora: Si se logra abrir el archivo retorna el 
 * 		puntero al archivo abierto, un puntero con valor NULL de lo
 * 		contrario.
 * */
FILE *openL ( char *pathLog ) {
	FILE *Log;
	Log = fopen ( pathLog, "a" );
	return Log;
}

/**
 * Comprueba el estado de apertura o creacion del archivo ingresado
 * @param FILE *bitacora: Es el puntero al archivo abierto
 * @return -1 Si el contenido de la *bitacora es NULL, 1 de lo contrario
 * */
int comprobarAbertura ( FILE *bitacora ) {
	if ( bitacora == NULL ) {
		return -1;
	}
	else {
		return 1;
	}
}

/**
 * Comprueba el estado de apertura o creacion del archivo ingresado
 * @param FILE *bitacora: Es el puntero al archivo abierto
 * @return -1 Si el contenido de la *bitacora es NULL, 1 de lo contrario
 * */
int verifyOpenFile ( FILE *log ) {
	if ( log == NULL ) {
		return -1;
	}
	else {
		return 1;
	}
}

/**
 * Crea el archivo solicitado o lo abre si este existe
 * @param *rutaBitacora path del archivo que se desea abrir/crear
 * @return struct Bitacora: Es la representacion de nuestro archivo
 * */
struct Bitacora abrirBitacora ( char *rutaBitacora ) {
	struct Bitacora resultadoApertura;
	resultadoApertura.archivo = abrirB ( rutaBitacora );
	resultadoApertura.estadoDeApertura = comprobarAbertura ( 
		resultadoApertura.archivo
	);
	return resultadoApertura;	
}

/**
 * Crea el archivo solicitado o lo abre si este existe
 * @param *rutaBitacora path del archivo que se desea abrir/crear
 * @return struct Bitacora: Es la representacion de nuestro archivo
 * */
struct LogFile openLog ( char *pathLog ) {
	struct LogFile result;
	result.file = abrirB ( pathLog );
	result.status = verifyOpenFile ( 
		result.file
	);
	return result;	
}

/**
 * Inserta una linea al final del texto abierto.
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * @param char *registro: Es el contenido que se le desea adicionar al
 * archivo.
 * */
void escribirRegistro ( struct Bitacora bitacoraAbierta, char *registro 
) {
	fprintf ( bitacoraAbierta.archivo, "%s", registro );
}


/**
 * Inserta una linea al final del texto abierto.
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * @param char *registro: Es el contenido que se le desea adicionar al
 * archivo.
 * */
void writeLine ( struct LogFile openedLog, char *line 
) {
	fprintf ( openedLog.file, "%s", line );
}

/** 
 * Cierra el archivo utilizado para la bitacora
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * */
void cerrarBitacora ( struct Bitacora bitacoraAbierta ){
	fclose ( bitacoraAbierta.archivo );
}

/** 
 * Cierra el archivo utilizado para la bitacora
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * */
void closeLog ( struct LogFile openedLog ){
	fclose ( openedLog.file );
}
 