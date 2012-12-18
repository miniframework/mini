mini framework
====

1.create app
   
      1  cd mini 
   
      2  php minic.php app ../test
   
      3  cd /etc/apache/httpd.cnf   
   
         set vhost \<VirtualHost\>\</VirtualHost\>
      
         set host  127.0.0.1 www.test.com
      
      4
         http://www.test.com/?app=site
      
         http://www.test.com/?app=admin


2.create model
      
      1 cp  test/config/config.xml test/config/config_dev.xml
      2 rm  config.xml
      2 ln -s config_dev.xml config.xml
      3 modify config_dev.xml <db></db>
      4 cd mini
      5 php minic.php shell ../test
      
            >>model create table
            
2.create curd             

            >>curd create table

