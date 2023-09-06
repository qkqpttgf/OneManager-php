[Readme EN](readme.md)  

# 注意：

  请关掉浏览器的翻译。  

  Releases只是当存档在用的，并不是最新代码。  

  请将设置中所有的设置项的说明都读一遍，有些问题就不用问了。  

---

# 部署到 Vercel  

### 官网

  https://vercel.com/  

### Demo

  https://onemanager-php.vercel.app/  

### 注意

> 1. 每次更改配置后都要等 30-50s 来确保部署成功；  
>
> 2. Vercel 每天限制 100 次部署。  

### 安装（英文）

  https://scfonedrive.github.io/Vercel/Deploy.html   

---

# 部署到 Replit  

### 官网

  https://repl.it/  
  https://replit.com/  

### Demo

  https://onemanager.qkqpttgf.repl.co/  

### 安装
A:
> 1. 点右上的 "+" 或左上的 "+ Create Repl"，点击`Import from Github`；
> 2. 在"GitHub URL"中输入`https://github.com/qkqpttgf/OneManager-php`，会自动弹出显示"Language"与"PHP Web Server"，点下面的"Import from Github"。  
> 3. 结束后，点上方的绿色 "Run" 按钮，右边会显示一个网页，你要在新窗口打开它来安装，不然不能登录。  

B:
> 1. 点右上的 "+" 或左上的 "+ Create Repl"，template中输入php，点选"PHP Web Server"，在"Title"里输入你想要的名称或者让它默认, 点下面的"+ Create Repl"。  
> 2. 结束后，在右边的Console或Shell里输入 `git clone https://github.com/qkqpttgf/OneManager-php && mv -b OneManager-php/* ./ && mv -b OneManager-php/.[^.]* ./ && rm -rf *~ && rm -rf OneManager-php` 敲回车运行。  
> 3. 点上方的绿色 "Run" 按钮，右边会显示一个网页，你要在新窗口打开它来安装，不然不能登录。  

---

# ~~部署到 Heroku~~  

## Dyno套餐不再免费  

### 官网

  https://heroku.com  

### Demo

  https://herooneindex.herokuapp.com/  

### 安装

> 给这个项目点star，然后fork，在Heroku创建一个app，然后点进Deploy页，在"Deployment method"处点"Connect GitHub"，选择你的fork。   

---

# 部署到 Glitch  

### 官网

  https://glitch.com/  

### Demo

  https://onemanager.glitch.me/  

### 安装

  点 [New Project] -> 点 [Import form Github] -> 粘贴 "https://github.com/qkqpttgf/OneManager-php" ，结束后，左上角点 [Show] -> [In a New Window]。  

---

# ~~部署到腾讯无服务器云函数 Serverless Cloud Function (SCF)~~  

## SCF要收套餐费用了  

### 官网

  https://cloud.tencent.com/product/scf  

### DEMO

  暂无  

### 注意事项

  SCF新增限制，环境变量整体最大4KB，所以最多添加4个盘（可以在安装时选择将配置保存在文件来避开限制）。  

### 安装

1. 进入函数服务，上方选择地区，然后点击新建。

2. 输入函数名称，选择模板函数，在模糊搜索中输入onedrive，大小写随意，选择那个【获取onedrive信息.....】，点下一步，在代码界面不用动，直接点完成。

3. 点击触发管理，创建触发器，触发方式改成API网关触发，底下勾选启用集成响应，提交。

4. 在触发管理中可以看到一个 访问路径，访问它，开始安装。 

    （重点：**勾选集成响应**）  

> **添加网盘时，SCF可能会反应不过来，不跳转到微软，导致添加失败，请不要删除这个盘，再添加一次相同标签的盘就可以了。**

----


# 部署到华为云函数工作流 Function Graph (FG)  

### 官网

  https://console.huaweicloud.com/functiongraph/  

### DEMO

  暂无  

### 注意事项

  FG中，环境变量整体大小为2KB，所以最多添加2个盘（一个onedrive一个aliyundrive）（可以在安装时选择将配置保存在文件来避开限制）。  

### 安装

1. 在函数列表，点右边创建函数  
2. 输入名称，选择运行时语言为PHP7.3，点上传ZIP文件，选择文件，然后点右边的创建函数（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。）  
3. 创建触发器：选API网关，安全认证选None，后端超时（毫秒）将5000改成30000，上面创建分组一下，其它的点点点  
4. 访问触发器给的url，开始安装
5. 在【触发器界面】点【触发器名称】，跳到API网关管理，右边【更多URL】，可以添加自定义域名，自定义域名后发现还是要 xxxx.com/函数名 来访问，点上方的【编辑】，第1页不用改，点【下一步】，**请求Path改成/**，注意匹配模式是前缀匹配，Method为ANY，然后不用点下一步了，点【立即完成】，然后去【发布】生效  

----

# 部署到阿里云函数计算 Function Compute (FC)  

### 官网 

  https://fc.console.aliyun.com/  

### DEMO

  无  

### 安装

1. 新建函数 -- HTTP函数  
2. 运行环境选择php7.2  
3. 触发器认证方式选择anonymous，请求方式里面，点一下GET，再点一下POST，最终框框里面有这2个  
4. 上传代码（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。）  
5. 触发器中点进去，找到配置自定义域名，点击前往，创建，路径中填 /* ，其它下拉选择。
6. 访问你的域名，开始安装  

---

# 部署到百度云函数计算 Cloud Function Compute (CFC)  

### 官网

  https://console.bce.baidu.com/cfc/#/cfc/functions  

### DEMO

  暂无  

### 注意事项

  **自定义域名需要另外使用API网关，并备案。**

### 安装

1. 在函数列表，点创建函数  
2. 创建方式改为空白函数，点下一步  
3. 输入名称，选择运行时为PHP7.2，点下一步  
4. 触发器：下拉选择HTTP触发器，URL路径填 /{filepath+} ，HTTP方法全选，身份验证：不验证，点提交  
5. 进入代码编辑页，编辑类型改上传函数ZIP包，选择文件（这里的ZIP文件不能直接用从Github上下载的ZIP文件，要将它解压后，去掉外层文件夹后，再压缩为ZIP。），开始上传  
6. 点击右边触发器，复制并访问提供的url，开始安装  

---

# 部署到VPS (Virtual Private Server) 或 空间  

### DEMO

暂无

### 安装

1. 启动web服务器，确保你能访问到。  

2. 开启伪静态(重写)功能，规则在.htaccess文件中，ngnix从里面复制，我们的目的是不管访问什么都让index.php来处理。  

3. 上传好代码。  

4. 使web身份可读写代码中的.data/config.php文件，推荐chmod 666 .data/config.php。  

5. 在浏览器中访问。  

----

# 特性  

  下载时，由程序解析出直链，浏览器直接从微软Onedrive服务器下载文件，服务器只消耗与微软通信的少量流量。  

  上传时，由程序生成上传url，浏览器直接向微软Onedrive的这个url上传文件，服务器只消耗与微软通信的少量流量。  

  设置中的 XXX_path 是Onedrive里面的路径，并不是你url里面的，程序会去你Onedrive里面找这个路径。  

  网站图标：将favicon.ico文件放在你要展示的目录中，确保 xxxxx.com/favicon.ico 可以访问到。  

  可以在文件列表显示head.md跟readme.md文件的内容。  

  游客上传目录（也叫图床目录），是指定一个目录，让游客可以上传文件，不限格式，不限大小。这个目录里面的内容不列清单（除非管理登录）。  

  如果目录中有index.html文件，只会输出显示html文件，不显示程序框架。  

  点击“时间”、“大小”，可以排序显示，点“文件”恢复原样。  

----

# 功能性文件

### favicon.ico

  放在第一个盘的显示目录（不一定是onedrive根目录）。  

### index.html

  将index.html以静态网页显示出来。  

### head.md

### readme.md

  以MD语法显示在顶部或底部。  

### head.omf

### foot.omf

  以html显示在顶部或底部（可以跑js）。  

----

# 捐赠

  https://paypal.me/qkqpttgf  

-----

# 群聊

  **请看完上面的中英双语再加群，谢谢！**  

### QQ 群:

  212088653  

### Telegram Group

  https://t.me/joinchat/I_RVc0bqxuxlT-d0cO7ozw  
