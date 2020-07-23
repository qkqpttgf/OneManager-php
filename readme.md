Install program first, then add onedrive in setup after login.  
先安装程序，登录后在设置中添加onedrive。  

# Deploy to Heroku  
Official: https://heroku.com  
Demo: https://herooneindex.herokuapp.com/  

How to Install: Click the button [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/qkqpttgf/OneManager-php) to Deploy a new app, or create an app then deploy via connect to your github fork.  


# Deploy to Tencent Serverless Cloud Function (SCF 腾讯无服务器云函数)  
Official: https://cloud.tencent.com/product/scf  
DEMO:  无  
注意：SCF新增限制，环境变量整体最大4KB，所以最多添加4个盘。  

How to Install:  无  
  
添加网盘时，SCF反应不过来，会添加失败，请不要删除，再添加一次相同的就可以了。  


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


# Deploy to Huawei cloud Function Graph (FG 华为云函数工作流)  
Official: https://console.huaweicloud.com/functiongraph/  
DEMO:  无  
注意：FG中，环境变量整体大小为2KB，所以最多添加2个盘。  

How to Install:  
1，在函数列表，点右边创建函数  
2，输入名称，选择运行时语言为PHP7.3，点上传ZIP文件，选择文件，然后点右边的创建函数（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。）  
3，创建触发器：选API网关，安全认证选None，后端超时（毫秒）将5000改成30000，上面创建分组一下，其它的点点点  
4，访问触发器给的url，开始安装  
5，在触发器界面点触发器名称，跳到API网关管理，右边更多URL，可以添加自定义域名，自定义域名后发现还是要 xxxx.com/函数名 来访问，点上方的编辑，第1页不用改，点下一步，请求Path改成/，注意匹配模式是前缀匹配，Method为ANY，然后不用点下一步了，点立即完成，然后去发布生效  


# Deploy to Aliyun Function Compute (FC 阿里云函数计算)  
Official: https://fc.console.aliyun.com/  
DEMO:  无  

How to Install:  
1，新建函数 -- HTTP函数  
2，运行环境选择php7.2  
3，触发器认证方式选择anonymous，请求方式里面，点一下GET，再点一下POST，最终框框里面有这2个  
4，上传代码  
5，触发器中点进去，找到配置自定义域名，点击前往，创建，路径中填 /* ，其它下拉选择。  
6，访问你的域名，开始安装  


# Features 特性  
When downloading files, the program produce a direct url, visitor download files from MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
下载时，由程序解析出直链，浏览器直接从微软Onedrive服务器下载文件，服务器只消耗与微软通信的少量流量。  
When uploading files, the program produce a direct url, visitor upload files to MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
上传时，由程序生成上传url，浏览器直接向微软Onedrive的这个url上传文件，服务器只消耗与微软通信的少量流量。  
The XXX_path in setting is the path in Onedrive, not in url, program will find the path in Onedrive.  
设置中的 XXX_path 是Onedrive里面的路径，并不是你url里面的，程序会去你Onedrive里面找这个路径。  
LOGO ICON: put your 'favicon.ico' in the path you showed, make sure xxxxx.com/favicon.ico can be visited.   
网站图标：将favicon.ico文件放在你要展示的目录中，确保 xxxxx.com/favicon.ico 可以访问到。  
Program will show content of 'readme.md' & 'head.md'.  
可以在文件列表显示head.md跟readme.md文件的内容。  
guest up path, is a folder that the guest can upload files, but can not be list (exclude admin).  
游客上传目录（也叫图床目录），是指定一个目录，让游客可以上传文件，不限格式，不限大小。这个目录里面的内容不列清单（除非管理登录）。  
If there is 'index.html' file, program will only show the content of 'index.html', not list the files.  
如果目录中有index.html文件，只会输出显示html文件，不显示程序框架。  
Click 'EditTime' or 'Size', the list will sort by time or size, Click 'File' can resume sort.  
点击“时间”、“大小”，可以排序显示，点“文件”恢复原样。  

QQ Group: 943919989 (请看完上面的中英双语再加群，谢谢！)  
Telegram Group: https://t.me/joinchat/I_RVc0bqxuxlT-d0cO7ozw  
