# Architecture

Nez follows a **skeleton-based construction model**:
- **Skeletons**: Encapsulate applications with dependencies.  
- **Self-similar design**: Enables modularity, portability, and reuse.  

## 🏗️ Core Components
1. **Design Layer**
   - Web-based **Nez GUI**.  
   - Graphical system design using blocks and skeletons.  
   - Generates JSON system configuration.

2. **Deployment Layer**
   - **Code generator**: Produces YAML, JSON, Dockerfiles.  
   - **Deployment orchestrator**: Instantiates containers across infrastructures.  

3. **Execution Layer**
   - **Execution manager**: Ensures order and orchestration.  
   - **Data orchestrator**: Moves data using a CDN with pub/sub model.  
   - Supports intra- and inter-institutional deployments.  

4. **Monitoring Manager**
   - Detects service failures.  
   - Relaunches or reassigns services to alternative resources.  

## 🔐 Security Mechanisms
- **Confidentiality**: AES-256 + CP-ABE encryption.  
- **Integrity**: SHA-256 checks.  
- **Availability**: Replication + Information Dispersal Algorithm (IDA).  
