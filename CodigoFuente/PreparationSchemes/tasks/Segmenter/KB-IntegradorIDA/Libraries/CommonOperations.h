#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/time.h> 
#include <sys/types.h>
#include <time.h> 
#ifndef FALSE
  #define FALSE 0
#endif
#ifndef TRUE
  #define TRUE 1
#endif

/**Declaration**/
double getMilliseconds(struct timeval start,struct timeval end);
void RandTimeInit(void);
float BestRand(double);

/**Implementation**/


/**
 * Compute transcurred milliseconds between two timers
 * */
double getMilliseconds(struct timeval start,struct timeval end){
	
	double millisecond = 0;
	
	millisecond = (
		(end.tv_usec - start.tv_usec)  + 
		((end.tv_sec - start.tv_sec) * 
		1000000.0f)
	);
	
	return millisecond/1000;
}


/**
 * Compute transcurred microseconds between two timers
 * */
long getMicroseconds(struct timeval start,struct timeval end){
	
	long microsecond = 0;
	
	microsecond = (
		(end.tv_usec - start.tv_usec)  + 
		((end.tv_sec - start.tv_sec) * 
		1000000.0f)
	);
	
	return microsecond;
}


/**
 * Start timer
 * */
void RandTimeInit(void){
   srand(time(NULL));
}


/**
 * Generate a random number
 * */
float BestRand(double max){
  return(1+max*rand()/(RAND_MAX+1.0));
}

