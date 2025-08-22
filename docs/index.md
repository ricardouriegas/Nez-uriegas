# Nez

**Nez** is a design-driven skeleton model that enables the construction of AI-based and analytic systems that run seamlessly across the **computing continuum** (from edge to cloud to HPC).


## Services and Containers

The following services are defined in the ```docker-compose.yml``` file:

### Core Services 
- ```valuechain```: Graphical interface for building AI-based systems  
- ```value-chain-api```: API for system construction  
- ```deployer```: Deployment service with validation mechanisms  
- ```value-chain-api-db```: Database for system construction  
- ```container-manager```: Container manager  

### Data Management 
- ```apigateway```: API Gateway  
- ```auth```: User authentication  
- ```db_auth```: User database  
- ```frontend```: Catalog management interface  
- ```db_pub_sub```: Pub/Sub service  
- ```pub_sub```: Pub/Sub database  
- ```db_metadata```: Metadata service  
- ```metadata```: Metadata database  

### Storage & Load Balancing
- ```storage1, storage2, storage3, storage4, storage5```: Storage services  
- ```balancing```: Load balancing service  

### Data Preparation & Recovery 
- ```sincronizador```  data uploading client
- ```PreparationSchemes```  NRFs manager

---

## Software Prerequisites

Nez services run on container technology to simplify deployment. Install the following dependencies:

- [Docker v20.10.23](https://docs.docker.com/engine/install/ubuntu/)  
- [Docker Compose v2.15.1](https://docs.docker.com/compose/install/)  
- [Java 17 or higher](https://www.oracle.com/java/technologies/javase/jdk17-archive-downloads.html)  