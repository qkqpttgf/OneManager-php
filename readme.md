# **Tfo**
###### A website for two-point filestorage online (tfo)(20210201). 

<img src="https://tfo.herokuapp.com/index/Uploaded/WebContents/Github/Tfo/Tfo.logo.svg" alt="Tfo's Logo" width="250" height="250"/>

## Featured theme files 主题特征性文件
<table> 
    <tbody>
        <tr> 
            <th>Type</th> 
            <th>Files</th> 
            <th>Postscript</th> 
        </tr> 
        <tr> 
            <td rowspan="4">Information</td> 
            <td>
                <a href="./app.json" title="app.json">app.json</a>
            </td>
            <td rowspan="4">Tfo's information is described in these files for deployment</td> 
        </tr>
        <tr>
            <td>
                <a href="./readme.md" title="readme.md">readme.md</a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="./conststr.php" title="conststr.php">conststr.php</a>
            </td>
        </tr>
                <tr>
            <td>
                <a href="./common.php" title="common.php">common.php</a>
            </td>
        </tr>
        <tr> 
            <td rowspan="8">UpdateAddress</td> 
            <td>
                <a href="./platform/Heroku.php" title="Heroku.php">Heroku.php</a>
            </td>
            <td rowspan="8">Address to update is changed for easy management and updating</td> 
        </tr>
                <tr>
            <td>
                <a href="./platform/AliyunFC.php" title="AliyunFC.php">AliyunFC.php</a>
            </td>
        </tr>
        <tr> 
            <td>
                <a href="./platform/BaiduCFC.php" title="BaiduCFC.php">BaiduCFC.php</a>
            </td> 
        </tr>
        <tr>
            <td>
                <a href="./platform/Normal.php" title="Normal.php">Normal.php</a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="./platform/TencentSCF_file.php" title="TencentSCF_file.php">TencentSCF_file.php</a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="./platform/TencentSCF_env.php" title="TencentSCF_env.php">TencentSCF_env.php</a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="./platform/HuaweiFG_file.php" title="HuaweiFG_file.php">HuaweiFG_file.php</a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="./platform/HuaweiFG_env.php" title="HuaweiFG_env.php">HuaweiFG_env.php</a>
            </td>
        </tr>
        <tr> 
            <td rowspan="1">Theme</td> 
            <td>
                <a href="./theme/tfo.html" title="tfo.html">tfo.html</a>
            </td>
            <td rowspan="1">Tfo's theme for OneManager-php</td> 
        </tr>
    </tbody>
</table>

具体地，您可以下载/复制体验一下或参考示例中运用tfo.html的[Li Share Storage Mini](https://tfo.herokuapp.com/ "Li Share Storage Mini")。今日诗词随多盘显示。目前，主题仍存在诸多问题，请谅解。PS:主题将要实现的功能有狠多，敬请期待...但是自愿附加组件如今日诗词、评论系统、站长工具、访问统计等涉及到其他平台的私密内容不包含在主题内，若有需要请自行寻找在后台添加。主题的php历史版本请到<a href="./theme/" title="Old Theme">Theme</a>文件夹查看.

## Deploy 部署
### Deploy to Heroku  
Official: https://heroku.com  
Demo: https://herooneindex.herokuapp.com/  

How to Install: Click the button [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/BingoKingo/Tfo) to Deploy a new app, or create an app then deploy via connect to your github fork.  


### Deploy to Glitch  
Official: https://glitch.com/  
Demo: https://onemanager.glitch.me/  

How to Install: New Project -> Import form Github -> paste "https://github.com/BingoKingo/Tfo", after done, Show -> In a New Window.  


### Deploy to Tencent Serverless Cloud Function (SCF 腾讯无服务器云函数)  
Official: https://cloud.tencent.com/product/scf  
DEMO:  无  
注意：SCF新增限制，环境变量整体最大4KB，所以最多添加4个盘。  

How to Install:  
1，进入函数服务，上方选择地区，然后点击新建。  
2，输入函数名称，选择模板函数，在模糊搜索中输入onedrive，大小写随意，选择那个【获取onedrive信息.....】，点下一步，在代码界面不用动，直接点完成。  
3，点击触发管理，创建触发器，触发方式改成API网关触发，底下勾选启用集成响应，提交。  
4，在触发管理中可以看到一个 访问路径，访问它，开始安装。  

（重点：勾选集成响应）  
  
添加网盘时，SCF可能会反应不过来，不跳转到微软，导致添加失败，请不要删除这个盘，再添加一次相同标签的盘就可以了。  


### Deploy to Virtual Private Server (VPS 或空间)  
DEMO:  无  
How to Install:  
    1.Start web service on your server (httpd or other), make sure you can visit it.  
    启动web服务器，确保你能访问到。  
    2.Make the rewrite works, the rule is in .htaccess file, make sure any query redirect to index.php.  
    开启伪静态(重写)功能，规则在.htaccess文件中，ngnix从里面复制，我们的目的是不管访问什么都让index.php来处理。  
    3.Upload code.  
    上传好代码。  
    4.Change the file .data/config.php can be read&write (666 is suggested).  
    使web身份可读写代码中的.data/config.php文件，推荐chmod 666 .data/config.php。    
    5.View the website in chrome or other.  
    在浏览器中访问。  


### Deploy to Huawei cloud Function Graph (FG 华为云函数工作流)  
Official: https://console.huaweicloud.com/functiongraph/  
DEMO:  无  
注意：FG中，环境变量整体大小为2KB，所以最多添加2个盘。  

How to Install:  
1，在函数列表，点右边创建函数  
2，输入名称，选择运行时语言为PHP7.3，点上传ZIP文件，选择文件，然后点右边的创建函数（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。）  
3，创建触发器：选API网关，安全认证选None，后端超时（毫秒）将5000改成30000，上面创建分组一下，其它的点点点  
4，访问触发器给的url，开始安装  
5，在触发器界面点触发器名称，跳到API网关管理，右边更多URL，可以添加自定义域名，自定义域名后发现还是要 xxxx.com/函数名 来访问，点上方的编辑，第1页不用改，点下一步，请求Path改成/，注意匹配模式是前缀匹配，Method为ANY，然后不用点下一步了，点立即完成，然后去发布生效  


### Deploy to Aliyun Function Compute (FC 阿里云函数计算)  
Official: https://fc.console.aliyun.com/  
DEMO:  无  

How to Install:  
1，新建函数 -- HTTP函数  
2，运行环境选择php7.2  
3，触发器认证方式选择anonymous，请求方式里面，点一下GET，再点一下POST，最终框框里面有这2个  
4，上传代码  
5，触发器中点进去，找到配置自定义域名，点击前往，创建，路径中填 /* ，其它下拉选择。  
6，访问你的域名，开始安装  


### Deploy to Baidu Cloud Function Compute (CFC 百度云函数计算)  
Official: https://console.bce.baidu.com/cfc/#/cfc/functions  
DEMO:  无  
自定义域名需要另外使用API网关，并备案。  

How to Install:  
1，在函数列表，点创建函数  
2，创建方式改为空白函数，点下一步  
3，输入名称，选择运行时为PHP7.2，点下一步  
4，触发器：下拉选择HTTP触发器，URL路径填 /{filepath+} ，HTTP方法全选，身份验证：不验证，点提交  
5，进入代码编辑页，编辑类型改上传函数ZIP包，选择文件（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。），开始上传  
6，点击右边触发器，复制并访问提供的url，开始安装  


## Features 特性  
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

## Functional files 功能性文件  
### favicon.ico  
put it in the showing home folder of FIRST disk (maybe not root of onedrive). 放在第一个盘的显示目录（不一定是onedrive根目录）。  
### index.html  
show content of index.html as html. 将index.html以静态网页显示出来。  
### head.md readme.md  
it will showed at top or bottom as markdown. 以MD语法显示在顶部或底部。  
### head.omf foot.omf  
it will showed at top or bottom as html (javascript works!). 以html显示在顶部或底部（可以跑js）。  