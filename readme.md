# Deploy to Virtual Private Server (VPS 或空间)  
DEMO:  无  
How to Install:  
    1.Start web service on your server (httpd or other), make sure you can visit it.  
    启动web服务器，确保你能访问到。  
    2.Make the rewrite works, the rule is in .htaccess file, make sure any query redirect to index.php.  
    开启伪静态(重写)功能，规则在.htaccess文件中，ngnix从里面复制，我们的目的是不管访问什么都让index.php来处理。  
    3.Upload code.  
    上传好代码。  
    4.Change the file config.php can be read&write (666 is suggested).  
    让代码中的config.php文件程序可读写，推荐chmod 666 config.php。  
    5.View the website in chrome or other.  
    在浏览器中访问。  
