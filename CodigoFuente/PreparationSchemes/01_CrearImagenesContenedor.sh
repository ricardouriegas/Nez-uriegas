#Make virtual container images of delivery
#docker build -t "delivery:file" delivery/.

#Make virtual container images of retrieval
#docker build -t "retrieval:file" retrieval/.

#Make virtual container images of indexing
docker build -t "sha3:file" tasks/hash/.

#Make virtual container images of indexing
docker build -t "indexing:file" tasks/indexing/.

#Make virtual container images of Segmenter and Integrator
docker build -f tasks/Segmenter/DockerfileKullaBase -t "kulla:base" tasks/Segmenter/.
docker build -f tasks/Segmenter/DockerfileKBSegmentadorIDA -t "kulla:code" tasks/Segmenter/.
docker build -f tasks/Segmenter/DockerfileKBIntegradorIDA -t "kulla:decode" tasks/Segmenter/.


#Make virtual container images of database in mongoDB
#docker-compose -p database -f database/docker-compose.yml up -d

#Pull LZ4 Image 
docker pull ddomizzi/lz4
docker tag ddomizzi/lz4 lz4:image

