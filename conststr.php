<?php

global $exts;
global $constStr;

$exts['img'] = ['ico', 'bmp', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'tif', 'tiff', 'png', 'heic', 'webp'];
$exts['music'] = ['mp3', 'wma', 'flac', 'wav', 'ogg'];
$exts['office'] = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
$exts['txt'] = ['txt', 'bat', 'sh', 'php', 'asp', 'js', 'json', 'html', 'c'];
$exts['video'] = ['mp4', 'webm', 'mkv', 'mov', 'flv', 'blv', 'avi', 'wmv'];
$exts['zip'] = ['zip', 'rar', '7z', 'gz', 'tar'];

$constStr = [
    'languages' => [
        'en-us' => 'English',
        'zh-cn' => '中文',
    ],
    'Week' => [
        0 => [
            'en-us' => 'Sunday',
            'zh-cn' => '星期日',
        ],
        1 => [
            'en-us' => 'Monday',
            'zh-cn' => '星期一',
        ],
        2 => [
            'en-us' => 'Tuesday',
            'zh-cn' => '星期二',
        ],
        3 => [
            'en-us' => 'Wednesday',
            'zh-cn' => '星期三',
        ],
        4 => [
            'en-us' => 'Thursday',
            'zh-cn' => '星期四',
        ],
        5 => [
            'en-us' => 'Friday',
            'zh-cn' => '星期五',
        ],
        6 => [
            'en-us' => 'Saturday',
            'zh-cn' => '星期六',
        ],
    ],
    'EnvironmentsDescription' => [
        'admin' => [
            'en-us' => 'The admin password, Login button will not show when empty',
            'zh-cn' => '管理密码，不添加时不显示登录页面且无法登录。',
        ],
        'adminloginpage' => [
            'en-us' => 'if set, the Login button will not display, and the login page no longer \'?admin\', it is \'?{this value}\'.',
            'zh-cn' => '如果设置，登录按钮及页面隐藏。管理登录的页面不再是\'?admin\'，而是\'?此设置的值\'。',
        ],
        'domain_path' => [
            'en-us' => 'more custom domain, format is a1.com:/dirto/path1|b2.com:/path2',
            'zh-cn' => '使用多个自定义域名时，指定每个域名看到的目录。格式为a1.com:/dirto/path1|b1.com:/path2，比private_path优先。',
        ],
        'imgup_path' => [
            'en-us' => 'Set guest upload dir, before set this, the files in this dir will show as normal.',
            'zh-cn' => '设置图床路径，不设置这个值时该目录内容会正常列文件出来，设置后只有上传界面，不显示其中文件（登录后显示）。',
        ],
        'passfile' => [
            'en-us' => 'The password of dir will save in this file.',
            'zh-cn' => '自定义密码文件的名字，可以是\'pppppp\'，也可以是\'aaaa.txt\'等等；列目录时不会显示，只有知道密码才能查看或下载此文件。密码是这个文件的内容，可以空格、可以中文；',
        ],
        'private_path' => [
            'en-us' => 'Show this Onedrive dir when through custom domain, default is \'/\'.',
            'zh-cn' => '使用自定义域名访问时，显示网盘文件的路径，不设置时默认为根目录。',
        ],
        'public_path' => [
            'en-us' => 'Show this Onedrive dir when through the long url of API Gateway; public show files less than private.',
            'zh-cn' => '使用API长链接访问时，显示网盘文件的路径，不设置时默认为根目录；不能是private_path的上级（public看到的不能比private多，要么看到的就不一样）。',
        ],
        'sitename' => [
            'en-us' => 'sitename',
            'zh-cn' => '网站的名称',
        ],
        'language' => [
            'en-us' => 'en-us or zh-cn',
            'zh-cn' => '目前en-us 或 zh-cn',
        ],
        'APIKey' => [
            'en-us' => 'the APIKey of Heroku',
            'zh-cn' => 'Heroku的API Key',
        ],
        'Onedrive_ver' => [
            'en-us' => 'Onedrive version',
            'zh-cn' => 'Onedrive版本',
        ],
    ],
    'SetSecretsFirst' => [
        'en-us' => 'Set APIKey in Config vars first! Then reflesh.',
        'zh-cn' => '先在环境变量设置APIKey！再刷新。',
    ],
    'RefleshtoLogin' => [
        'en-us' => '<font color="red">Reflesh</font> and login.',
        'zh-cn' => '请<font color="red">刷新</font>页面后重新登录',
    ],
    'AdminLogin' => [
        'en-us' => 'Admin Login',
        'zh-cn' => '管理登录',
    ],
    'LoginSuccess' => [
        'en-us' => 'Login Success!',
        'zh-cn' => '登录成功，正在跳转',
    ],
    'InputPassword' => [
        'en-us' => 'Input Password',
        'zh-cn' => '输入密码',
    ],
    'Login' => [
        'en-us' => 'Login',
        'zh-cn' => '登录',
    ],
    'encrypt' => [
        'en-us' => 'Encrypt',
        'zh-cn' => '加密',
    ],
    'SetpassfileBfEncrypt' => [
        'en-us' => 'Set \'passfile\' in Environments before encrypt',
        'zh-cn' => '先在环境变量设置passfile才能加密',
    ],
    'updateProgram' => [
        'en-us' => 'Update Program',
        'zh-cn' => '一键更新',
    ],
    'UpdateSuccess' => [
        'en-us' => 'Program update Success!',
        'zh-cn' => '程序升级成功！',
    ],
    'Setup' => [
        'en-us' => 'Setup',
        'zh-cn' => '设置',
    ],
    'Back' => [
        'en-us' => 'Back',
        'zh-cn' => '返回',
    ],
    'NotNeedUpdate' => [
        'en-us' => 'Not Need Update',
        'zh-cn' => '不需要更新',
    ],
    'Home' => [
        'en-us' => 'Home',
        'zh-cn' => '首页',
    ],
    'NeedUpdate' => [
        'en-us' => 'Program can update<br>Click setup in Operate at top.',
        'zh-cn' => '可以升级程序<br>在上方管理菜单中<br>进入设置页面升级',
    ],
    'Operate' => [
        'en-us' => 'Operate',
        'zh-cn' => '管理',
    ],
    'Logout' => [
        'en-us' => 'Logout',
        'zh-cn' => '登出',
    ],
    'Create' => [
        'en-us' => 'Create',
        'zh-cn' => '新建',
    ],
    'Download' => [
        'en-us' => 'download',
        'zh-cn' => '下载',
    ],
    'ClicktoEdit' => [
        'en-us' => 'Click to edit',
        'zh-cn' => '点击后编辑',
    ],
    'Save' => [
        'en-us' => 'Save',
        'zh-cn' => '保存',
    ],
    'FileNotSupport' => [
        'en-us' => 'File not support preview.',
        'zh-cn' => '文件格式不支持预览',
    ],
    'File' => [
        'en-us' => 'File',
        'zh-cn' => '文件',
    ],
    'ShowThumbnails' => [
        'en-us' => 'Thumbnails',
        'zh-cn' => '图片缩略',
    ],
    'EditTime' => [
        'en-us' => 'EditTime',
        'zh-cn' => '修改时间',
    ],
    'Size' => [
        'en-us' => 'Size',
        'zh-cn' => '大小',
    ],
    'Rename' => [
        'en-us' => 'Rename',
        'zh-cn' => '重命名',
    ],
    'Move' => [
        'en-us' => 'Move',
        'zh-cn' => '移动',
    ],
    'Delete' => [
        'en-us' => 'Delete',
        'zh-cn' => '删除',
    ],
    'PrePage' => [
        'en-us' => 'PrePage',
        'zh-cn' => '上一页',
    ],
    'NextPage' => [
        'en-us' => 'NextPage',
        'zh-cn' => '下一页',
    ],
    'Upload' => [
        'en-us' => 'Upload',
        'zh-cn' => '上传',
    ],
    'Submit' => [
        'en-us' => 'Submit',
        'zh-cn' => '确认',
    ],
    'Close' => [
        'en-us' => 'Close',
        'zh-cn' => '关闭',
    ],
    'InputPasswordUWant' => [
        'en-us' => 'Input Password you Want',
        'zh-cn' => '输入想要设置的密码',
    ],
    'ParentDir' => [
        'en-us' => 'Parent Dir',
        'zh-cn' => '上一级目录',
    ],
    'Folder' => [
        'en-us' => 'Folder',
        'zh-cn' => '文件夹',
    ],
    'Name' => [
        'en-us' => 'Name',
        'zh-cn' => '名称',
    ],
    'Content' => [
        'en-us' => 'Content',
        'zh-cn' => '内容',
    ],
    'CancelEdit' => [
        'en-us' => 'Cancel Edit',
        'zh-cn' => '取消编辑',
    ],
    'GetFileNameFail' => [
        'en-us' => 'Fail to Get File Name!',
        'zh-cn' => '获取文件名失败！',
    ],
    'GetUploadLink' => [
        'en-us' => 'Get Upload Link',
        'zh-cn' => '获取上传链接',
    ],
    'UpFileTooLarge' => [
        'en-us' => 'The File is too Large!',
        'zh-cn' => '文件过大，终止上传。',
    ],
    'UploadStart' => [
        'en-us' => 'Upload Start',
        'zh-cn' => '开始上传',
    ],
    'UploadStartAt' => [
        'en-us' => 'Start At',
        'zh-cn' => '开始于',
    ],
    'ThisTime' => [
        'en-us' => 'This Time',
        'zh-cn' => '本次',
    ],
    'LastUpload' => [
        'en-us' => 'Last time Upload',
        'zh-cn' => '上次上传',
    ],
    'AverageSpeed' => [
        'en-us' => 'AverageSpeed',
        'zh-cn' => '平均速度',
    ],
    'CurrentSpeed' => [
        'en-us' => 'CurrentSpeed',
        'zh-cn' => '即时速度',
    ],
    'Expect' => [
        'en-us' => 'Expect',
        'zh-cn' => '预计还要',
    ],
    'EndAt' => [
        'en-us' => 'End At',
        'zh-cn' => '结束于',
    ],
    'UploadErrorUpAgain' => [
        'en-us' => 'Maybe error, do upload again.',
        'zh-cn' => '可能出错，重新上传。',
    ],
    'UploadComplete' => [
        'en-us' => 'Upload Complete',
        'zh-cn' => '上传完成',
    ],
    'UploadFail23' => [
        'en-us' => 'Upload Fail, contain #.',
        'zh-cn' => '目录或文件名含有#，上传失败。',
    ],
    'defaultSitename' => [
        'en-us' => 'Set sitename in Environments',
        'zh-cn' => '请在环境变量添加sitename',
    ],
    'MayinEnv' => [
        'en-us' => 'The \'Onedrive_ver\' may in Environments',
        'zh-cn' => 'Onedrive_ver应该已经写入环境变量',
    ],
    'Wait' => [
        'en-us' => 'Wait',
        'zh-cn' => '稍等',
    ],
    'WaitJumpIndex' => [
        'en-us' => 'Wait 5s jump to Home page',
        'zh-cn' => '等5s跳到首页',
    ],
    'JumptoOffice' => [
        'en-us' => 'Login Office and Get a refresh_token',
        'zh-cn' => '跳转到Office，登录获取refresh_token',
    ],
    'OndriveVerMS' => [
        'en-us' => 'default(Onedrive, Onedrive for business)',
        'zh-cn' => '默认（支持商业版与个人版）',
    ],
    'OndriveVerCN' => [
        'en-us' => 'Onedrive in China',
        'zh-cn' => '世纪互联版',
    ],
    'OndriveVerMSC' =>[
        'en-us' => 'default but use customer app id & secret',
        'zh-cn' => '国际版，自己申请应用ID与机密',
    ],
    'GetSecretIDandKEY' =>[
        'en-us' => 'Get customer app id & secret',
        'zh-cn' => '申请应用ID与机密',
    ],
    'Reflesh' => [
        'en-us' => 'Reflesh',
        'zh-cn' => '刷新',
    ],
    'SelectLanguage' => [
        'en-us' => 'Select Language',
        'zh-cn' => '选择语言',
    ],
];

?>
