[中文说明](readme.md)

# NOTICE: the release is used as archive. 

Please read the descriptions of settings before raising an issue.  

# Deploy to Heroku  
Official: https://heroku.com  
Demo: https://herooneindex.herokuapp.com/  

How to Install:   
> ~~Click the button [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy) to Deploy a new app~~(`"We couldn't deploy your app because the source code violates the Salesforce Acceptable Use and External-Facing Services Policy."`)  
> Fork this project, create a heroku app, then turn to Deploy tab, deploy via connect to your github fork.   


# Deploy to Glitch  
Official: https://glitch.com/  
Demo: https://onemanager.glitch.me/  

How to Install: New Project -> Import form Github -> paste "https://github.com/qkqpttgf/OneManager-php", after done, Show -> In a New Window.  


# Deploy to Vercel  
Official: https://vercel.com/  
Demo: None  
Notice: 

> 1, you must wait 30-50s to make sure deploy READY after change config;  
> 2, Vercel limit 100 deploy every day.  

How to Install: https://scfonedrive.github.io/Vercel/Deploy.html .  


# Deploy to Tencent Serverless Cloud Function (SCF)  
Official: https://cloud.tencent.com/product/scf  
DEMO:  None

Notice:  SCF has new restrictions. The overall maximum environment variable is 4KB, so you can add up to 4 disks.

How to Install:  

1，Enter the function service, select the region above, and then click New.  
2，Enter the function name, select the template function, enter onedrive in the fuzzy search, any case, select the one [Get onedrive information.....], click Next, do not operate in the Code interface, just click Finish.  
3，Click Trigger Management, create a trigger, change the trigger method to API Gateway trigger, tick Enable integrated response below, and submit.  
4，You can see an access path in the trigger management, visit it, and start the installation.  

(Key: check Enable integrated response)

When adding a disk, SCF may not be able to respond, and does not jump to Microsoft's website, causing the addition to fail. Please do not delete this disk, just add a disk with the same label again.


# Deploy to Huawei cloud Function Graph (FG)  
Official: https://console.huaweicloud.com/functiongraph/  
DEMO:  None  
Notice:  In FG, the overall size of environment variables is 2KB, so at most 2 disks (one onedrive and one aliyundrive) can be added.  

How to Install:  
  1，In the function list, click Create a function on the right,  
  2，Enter the name, select the PHP7.3 as runtime language, click Upload ZIP file, select the file, and click the create function on the right (The ZIP file downloaded from GitHub cannot be directly used here. After decompressing it, first removing the outer folder, then compress it to ZIP.)  
  3，Create a trigger, select API gateway, select None for security authentication, change the backend timeout from 5000ms to 30000ms, then create a group above. After that, just click ,  
  4，Visit the url given by the trigger and start the installation,  
  5，Click the trigger name on the trigger interface, jump to API gateway management, click more URLs on the right, you can add a custom domain.After customizing the domain, if you still need xxxx.com/function name to access, click the edit above , click Next (page 1 does not need to be changed,) change the Request Path to `/`, note that the matching mode is prefix matching, Method is ANY, and then no need to click Next, click Finish now, and then go to publish to take effect.


# Deploy to Aliyun Function Compute (FC)  
Official: https://fc.console.aliyun.com/  
DEMO:  None  

How to Install:  
  1，New function - HTTP function,  
  2，Choose php 7.2 for runtime environment,  
  3，Select anonymous as the trigger authentication method. Go to the request method, click GET and then POST(in the final box),  
  4，Upload the code  
  5，Click in the trigger, go to the custom domain configuration , click Go, Create, fill in `/*` in the path, and others are drop-down options.  
  6，Visit your domain and start the installation.  


# Deploy to Baidu Cloud Function Compute (CFC)  
Official: https://console.bce.baidu.com/cfc/#/cfc/functions  
DEMO:  None  
Using custom domain needs to use an API gateway and complete ICP filing.

How to Install:  
  1，In the function list, click Create function,  
  2，Change the creation method to a blank function, click Next,  
  3，Enter the name, select PHP7.2 for runtime environment, and click Next,  
  4，Trigger: drop down to select HTTP trigger, fill in URL path `/{filepath+}`, select all HTTP methods, set authentication to no verification, click submit,  
  5，Enter the code editing page, change the editing type to upload the function ZIP package, and select the file (The ZIP file downloaded from GitHub cannot be directly used here. After decompressing it, first removing the outer folder, then compress it to ZIP), then start uploading,  
  6，Click the trigger on the right, copy and visit the provided url to start the installation.  


# Deploy to Virtual Private Server  
DEMO:  None  
How to Install:  
    1.Start web service on your server (httpd or other), make sure you can visit it.  
    2.Make the rewrite works (the rule is in .htaccess file, If you are using nginx, then copy the rules from this file), make sure any query redirect to index.php.   
    3.Upload code.  
    4.Change the file .data/config.php can be read&write (666 is suggested).    
    5.View the website in a web browser.  


# Features 
When downloading files, the program produce a direct url, visitor download files from MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
When uploading files, the program produce a direct url, visitor upload files to MS OFFICE via the direct url, the server expend a few bandwidth in produce.  
The XXX_path in setting is the path in Onedrive, not in url, program will find the path in Onedrive.  
LOGO ICON: put your 'favicon.ico' in the path you showed, make sure xxxxx.com/favicon.ico can be visited.   
Program will show content of 'readme.md' & 'head.md'.  
guest up path, is a folder that the guest can upload files, but can not be list (exclude admin).  
If there is 'index.html' file, program will only show the content of 'index.html', not list the files.  
Click 'EditTime' or 'Size', the list will sort by time or size, Click 'File' can resume sort.  

# Functional files
### favicon.ico  
put it in the showing home folder of FIRST disk (maybe not root of onedrive). 
### index.html  
show content of index.html as html. 
### head.md readme.md  
it will showed at top or bottom as markdown. 
### head.omf foot.omf  
it will showed at top or bottom as html (javascript works). 

# A cup of coffee  
https://paypal.me/qkqpttgf  

# Chat  
QQ Group: 212088653 (If you want to join please read the README first, thank you.)  
Telegram Group: https://t.me/joinchat/I_RVc0bqxuxlT-d0cO7ozw  
