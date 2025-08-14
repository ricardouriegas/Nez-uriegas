CC:= gcc
LIBCC:= -c 
CFLAGS:= -g -Wall -fPIC
MATH:= -lm
RM:= rm
HILOS:= -lpthread

all: ALInputFS ALOutputFS PL_Ida_O1FS4N ALOutputN PL_SegmentadorMemoria_SHM

##Generacion de ejecutables
ALInputFS: ALInputFS.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALInputFS ALInputFS.o Libraries/AccessLayer.o
	$(RM) ALInputFS.o

ALOutputFS: ALOutputFS.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALOutputFS ALOutputFS.o Libraries/AccessLayer.o
	$(RM) ALOutputFS.o

ALOutputN: ALOutputN.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o ALOutputN ALOutputN.o Libraries/AccessLayer.o
	$(RM) ALOutputN.o

PL_Ida_O1FS4N: PL_Ida_O1FS4N.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o PL_Ida_O1FS4N PL_Ida_O1FS4N.o Libraries/AccessLayer.o
	$(RM) PL_Ida_O1FS4N.o

PL_SegmentadorMemoria_SHM: PL_SegmentadorMemoria_SHM.o Libraries/AccessLayer.o
	$(CC) $(CFLAGS) -o PL_SegmentadorMemoria_SHM PL_SegmentadorMemoria_SHM.o Libraries/AccessLayer.o $(HILOS)
	$(RM) PL_SegmentadorMemoria_SHM.o


#Generacion de archivos .o
ALInputFS.o: ALInputFS.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALInputFS.o ALInputFS.c $(MATH)

ALOutputFS.o: ALOutputFS.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALOutputFS.o ALOutputFS.c $(MATH)

ALOutputN.o: ALOutputN.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o ALOutputN.o ALOutputN.c $(MATH)

PL_Ida_O1FS4N.o: PL_Ida_O1FS4N.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o PL_Ida_O1FS4N.o PL_Ida_O1FS4N.c $(MATH)

PL_SegmentadorMemoria_SHM.o: PL_SegmentadorMemoria_SHM.c Libraries/AccessLayer.o
	$(CC) $(LIBCC) $(CFLAGS) -Wcpp -o PL_SegmentadorMemoria_SHM.o PL_SegmentadorMemoria_SHM.c $(MATH) $(HILOS)


clean:
	$(RM) ALInputFS
	$(RM) PL_SegmentadorMemoria_SHM
	$(RM) PL_Ida_O1FS4N
	$(RM) ALOutputFS
	$(RM) ALOutputN