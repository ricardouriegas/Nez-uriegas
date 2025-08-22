echo "ServerName localhost
<IfModule mpm_worker_module> 
 ServerLimit             250
 StartServers             10 
 MinSpareThreads          75 
 MaxSpareThreads         250 
 ThreadLimit              64 
 ThreadsPerChild          32
 MaxClients             8000
 MaxRequestsPerChild   10000 
</IfModule>" >> /etc/apache2/apache2.conf