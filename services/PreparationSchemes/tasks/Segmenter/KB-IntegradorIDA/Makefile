CC:= gcc
LIBCC:= -c 
CFLAGS:= -g -Wall 
MATH:= -lm
RM:= rm
HILOS:= -lpthread

all: ALGetContentFS ALOutputN ALInputFS PL_IDADecode PL_Integrator

##Generacion de ejecutables
ALGetContentFS: ALGetContentFS.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALGetContentFS ALGetContentFS.o Libraries/AccessLayer.o
	$(RM) ALGetContentFS.o

ALOutputN: ALOutputN.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALOutputN ALOutputN.o Libraries/AccessLayer.o
	$(RM) ALOutputN.o

ALInputFS: ALInputFS.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALInputFS ALInputFS.o Libraries/AccessLayer.o
	$(RM) ALInputFS.o

PL_IDADecode: PL_IDADecode.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o PL_IDADecode PL_IDADecode.o Libraries/AccessLayer.o
	$(RM) PL_IDADecode.o


#ALOutputFS: ALOutputFS.o Libraries/AccessLayer.o
#	$(CC) $(CFLAGS) -o ALOutputFS ALOutputFS.o Libraries/AccessLayer.o
#	$(RM) ALOutputFS.o

#PL_Ida_O1FS4N: PL_Ida_O1FS4N.o Libraries/AccessLayer.o
#	$(CC) $(CFLAGS) -o PL_Ida_O1FS4N PL_Ida_O1FS4N.o Libraries/AccessLayer.o
#	$(RM) PL_Ida_O1FS4N.o

PL_Integrator: PL_Integrator.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o PL_Integrator PL_Integrator.o Libraries/AccessLayer.o $(HILOS)
	$(RM) PL_Integrator.o


#Generacion de archivos .o
ALGetContentFS.o: ALGetContentFS.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALGetContentFS.o ALGetContentFS.c $(MATH)


ALOutputN.o: ALOutputN.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALOutputN.o ALOutputN.c $(MATH)

ALInputFS.o: ALInputFS.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALInputFS.o ALInputFS.c $(MATH)

PL_IDADecode.o: PL_IDADecode.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o PL_IDADecode.o PL_IDADecode.c $(MATH)

#PL_Ida_O1FS4N.o: PL_Ida_O1FS4N.c Libraries/AccessLayer.o
#	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o PL_Ida_O1FS4N.o PL_Ida_O1FS4N.c $(MATH)


PL_Integrator.o: PL_Integrator.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o PL_Integrator.o PL_Integrator.c $(MATH) $(HILOS)

#ALOutputFS.o: ALOutputFS.c Libraries/AccessLayer.o
#	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALOutputFS.o ALOutputFS.c $(MATH)



clean:
	$(RM) ALGetContentFS
	$(RM) ALOutputN
	$(RM) ALInputFS
	$(RM) PL_IDADecode
	$(RM) PL_Integrator