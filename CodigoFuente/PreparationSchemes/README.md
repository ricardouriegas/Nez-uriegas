# Data preparation/retrieval scheme 


# Preconfiguration

Generate the Docker images executing ```01_CrearImagenesContenedor.sh``` as any shell file
```bash
bash 01_CrearImagenesContenedor.sh
```

To install and configure SkyCDS please refers to the [SkyCDS repository](http://disys0.tamps.cinvestav.mx:9090/skycds/services.git). 

# Preparation scheme
The preparation scheme applies IDA over the data. The segments generated are upload by using SkyCDS. You must first prepare and configure an installation of SkyCDS. When SkyCDS is configured, you must created an organization, an user, and five catalogs, saving their corresponding tokens.

The preparation scheme is placed in the ```delivery``` folder. Edit the  ```config.db``` file with the SkyCDS configurations. Edit the ```config.cfg``` with the configuration of the scheme by indicating the number of workers in the patterns, the folder to prepare, the tokens of the catalogs, and the SkyCDS organization, user, toker user, access token and apikey.

Compile the scheme by using the makefile, and the execute the scheme as follows

```bash
make
./main
```
