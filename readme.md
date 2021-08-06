# NOTICE: the release is used as archive. 
# 注意：release只是用来存档的。
Please read the descriptions of settings before raising an issue.  
请将设置中所有的设置项的说明都读一遍，有些问题就不用问了。  

# Deploy to Heroku  
Official: https://heroku.com  
Demo: https://herooneindex.herokuapp.com/  

How to Install:   
> ~~Click the button [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/qkqpttgf/OneManager-php) to Deploy a new app~~(`"We couldn't deploy your app because the source code violates the Salesforce Acceptable Use and External-Facing Services Policy."`)  
> Fork this project, create a heroku app, then turn to Deploy tab, deploy via connect to your github fork.   


# Deploy to Glitch  
Official: https://glitch.com/  
Demo: https://onemanager.glitch.me/  

How to Install: New Project -> Import form Github -> paste "https://github.com/qkqpttgf/OneManager-php", after done, Show -> In a New Window.  


# Deploy to Vercel  
Official: https://vercel.com/  
Demo: null  
Notice: 
> 1. you must wait 30-50s to make sure deploy READY after change config;  
> 2. the max size of environment is 4k, so you can add 3 onedrive or less;  
> 3. Vercel limit 100 deploy every day.  

How to Install: https://scfonedrive.github.io/Vercel/Deploy.html .  


# Deploy to Tencent Serverless Cloud Function (SCF 腾讯无服务器云函数)  
Official: https://cloud.tencent.com/product/scf  
DEMO:  无  
注意：SCF新增限制，环境变量整体最大`4KB`，所以最多添加4个盘。  

How to Install:  
1. 进入函数服务，上方选择地区，然后点击新建。  
2. 输入函数名称，选择`模板函数`，在模糊搜索中输入`onedrive`，大小写随意，选择那个`获取onedrive信息.....`，点`下一步`，在代码界面不用动，直接点`完成`。  
3. 点击`触发管理`，创建触发器，触发方式改成`API网关触发`，底下勾选`启用集成响应`，提交。  
4. 在触发管理中可以看到一个`访问路径`，访问它，开始安装。  

**（重点：勾选集成响应）**  
  
**添加网盘时，SCF可能会反应不过来，不跳转到微软，导致添加失败，请不要删除这个盘，再添加一次相同标签的盘就可以了。**  


# Deploy to Huawei cloud Function Graph (FG 华为云函数工作流)  
Official: https://console.huaweicloud.com/functiongraph/  
DEMO:  无  
注意：FG中，环境变量整体大小为`2KB`，所以最多添加2个盘（一个onedrive一个aliyundrive）。  

How to Install:  
  1. 在函数列表，点右边`创建函数`  
  2. 输入名称，选择运行时语言为`PHP7.3`，点`上传ZIP文件`，选择文件，然后点右边的`创建函数`（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。）  
  3, 创建触发器：选`API网关`，安全认证选`None`，后端超时（毫秒）将`5000`改成`30000`，上面创建分组一下，其它的点点点  
  4. 访问触发器给的url，开始安装  
  5. 在触发器界面点`触发器名称`，跳到API网关管理，右边更多URL，可以添加自定义域名，自定义域名后发现还是要 `xxxx.com/函数名` 来访问，点上方的`编辑`，第1页不用改，点下一步，请求Path改成`/`，注意匹配模式是前缀匹配，Method为`ANY`，然后不用点下一步了，点立即完成，然后去发布生效  


# Deploy to Aliyun Function Compute (FC 阿里云函数计算)  
Official: https://fc.console.aliyun.com/  
DEMO:  无  

How to Install:  
  1. `新建函数` -- `HTTP函数`  
  2. 运行环境选择`php7.2'  
  3. 触发器认证方式选择`anonymous`，请求方式里面，点一下`GET`，再点一下`POST`，最终框框里面有这2个  
  4. 上传代码  
  5. 触发器中点进去，找到`配置自定义域名`，点击`前往`，`创建`，路径中填 `/*` ，其它下拉选择。  
  6. 访问你的域名，开始安装  


# Deploy to Baidu Cloud Function Compute (CFC 百度云函数计算)  
Official: https://console.bce.baidu.com/cfc/#/cfc/functions  
DEMO:  无  
自定义域名需要另外使用`API网关`，并备案。  

How to Install:  
  1. 在函数列表，点`创建函数`  
  2. 创建方式改为`空白函数`，点下一步  
  3. 输入名称，选择运行时为`PHP7.2`，点下一步  
  4. 触发器：下拉选择`HTTP触发器`，URL路径填 `/{filepath+}` ，HTTP方法`全选`，身份验证：`不验证`，点提交  
  5. 进入代码编辑页，编辑类型改`上传函数ZIP包`，选择文件（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。），开始上传  
  6. 点击右边`触发器`，复制并访问提供的url，开始安装  


# Deploy to Virtual Private Server (VPS 或空间)  
DEMO:  无  
How to Install:  
  1. Start web service on your server (httpd or other), make sure you can visit it.  
  启动web服务器，确保你能访问到。  
  2. Make the rewrite works, the rule is in .htaccess file, make sure any query redirect to index.php.  
  开启`伪静态(重写)`功能，规则在`.htaccess`文件中，ngnix从里面复制，我们的目的是不管访问什么都让`index.php`来处理。  
  3. Upload code.  
  上传好代码。  
  4. Change the file `.data/config.php` can be **read&write** ('666' is suggested).  
  使web身份可**读写**代码中的`.data/config.php`文件，推荐`chmod 666 .data/config.php'。  
  6. View the website in chrome or other.  
  在浏览器中访问。  


# Features 特性  
- When downloading files, the program produce a direct url, visitor download files from MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
下载时，由程序解析出直链，浏览器直接从微软Onedrive服务器下载文件，服务器只消耗与微软通信的少量流量。  
- When uploading files, the program produce a direct url, visitor upload files to MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
上传时，由程序生成上传url，浏览器直接向微软Onedrive的这个url上传文件，服务器只消耗与微软通信的少量流量。  
- The `XXX_path` in setting is the path in Onedrive, not in url, program will find the path in Onedrive.  
设置中的 `XXX_path` 是Onedrive里面的路径，并不是你url里面的，程序会去你Onedrive里面找这个路径。  
- LOGO ICON: put your `favicon.ico` in the path you showed, make sure `xxxxx.com/favicon.ico` can be visited.   
网站图标：将`favicon.ico`文件放在你要展示的目录中，确保 `xxxxx.com/favicon.ico` 可以访问到。  
- Program will show content of `readme.md` & `head.md`.  
可以在文件列表显示`head.md`和`readme.md`文件的内容。  
- Guest up path, is a folder that the guest can upload files, but can not be list (exclude admin).  
游客上传目录（也叫图床目录），是指定一个目录，让游客可以上传文件，不限格式，不限大小。这个目录里面的内容不列清单（除非管理登录）。  
- If there is `index.html` file, program will only show the content of `index.html`, not list the files.  
如果目录中有`index.html`文件，只会输出显示html文件，不显示程序框架。  
- Click `EditTime` or `Size`, the list will sort by time or size, Click `File` can resume sort.  
点击“时间”、“大小”，可以排序显示，点“文件”恢复原样。  

# Functional files 功能性文件  
### favicon.ico  
put it in the showing home folder of **FIRST** disk (maybe not root of onedrive). 放在**第一个**盘的显示目录（不一定是onedrive根目录）。  
### index.html  
show content of `index.html` as stahtml. 将`index.html`以静态网页显示出来。  
### head.md readme.md  
it will showed at top or bottom as markdown. 以MD语法显示在顶部或底部。  
### head.omf foot.omf  
it will showed at top or bottom as static html. (javascript works!). 以html显示在顶部或底部（可以跑js）。  

# A cup of coffee  
paypal.me/qkqpttgf  

# Chat  
QQ Group: 212088653 (请看完上面的中英双语再加群，谢谢！)  
Telegram Group: https://t.me/joinchat/I_RVc0bqxuxlT-d0cO7ozw  
