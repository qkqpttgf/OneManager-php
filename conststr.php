<?php
global $exts;
global $constStr;

$exts['img'] = ['ico', 'bmp', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'tif', 'tiff', 'png', 'heic', 'webp'];
$exts['music'] = ['mp3', 'wma', 'flac', 'wav', 'ogg'];
$exts['office'] = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
$exts['txt'] = ['txt', 'bat', 'sh', 'php', 'asp', 'js', 'json', 'html', 'c'];
$exts['video'] = ['mp4', 'webm', 'mkv', 'mov', 'flv', 'blv', 'avi', 'wmv', 'm3u8'];
$exts['zip'] = ['zip', 'rar', '7z', 'gz', 'tar'];

$constStr = [
    'languages' => [
        'en-us' => 'English',
        'zh-cn' => '中文',
        'ja' => '日本語',
    ],
    'Week' => [
        'en-us' => [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ],
        'zh-cn' => [
            0 => '星期日',
            1 => '星期一',
            2 => '星期二',
            3 => '星期三',
            4 => '星期四',
            5 => '星期五',
            6 => '星期六',
        ],
        'ja' => [
            0 => '日曜日',
            1 => '月曜日',
            2 => '火曜日',
            3 => '水曜日',
            4 => '木曜日',
            5 => '金曜日',
            6 => '土曜日',
        ],
    ],
    'EnvironmentsDescription' => [
        'en-us' => [
            'admin' => 'The admin password, Login button will not show when empty',
            'adminloginpage' => 'if set, the Login button will not display, and the login page no longer \'?admin\', it is \'?{this value}\'.',
            'domain_path' => 'more custom domain, format is a1.com:/dirto/path1|b2.com:/path2',
            'guestup_path' => 'Set guest upload dir, before set this, the files in this dir will show as normal.',
            'passfile' => 'The password of dir will save in this file.',
            'public_path' => 'Show this Onedrive dir when through the long url of API Gateway; public show files less than private.',
            'sitename' => 'sitename',
            'Onedrive_ver' => 'Onedrive version',
        ],
        'zh-cn' => [
            'admin' => '管理密码，不添加时不显示登录页面且无法登录。',
            'adminloginpage' => '如果设置，登录按钮及页面隐藏。管理登录的页面不再是\'?admin\'，而是\'?此设置的值\'。',
            'domain_path' => '使用多个自定义域名时，指定每个域名看到的目录。格式为a1.com:/dirto/path1|b1.com:/path2，比private_path优先。',
            'guestup_path' => '设置游客上传路径（图床路径），不设置这个值时该目录内容会正常列文件出来，设置后只有上传界面，不显示其中文件（登录后显示）。',
            'passfile' => '自定义密码文件的名字，可以是\'pppppp\'，也可以是\'aaaa.txt\'等等；列目录时不会显示，只有知道密码才能查看或下载此文件。密码是这个文件的内容，可以空格、可以中文；',
            'public_path' => '使用API长链接访问时，显示网盘文件的路径，不设置时默认为根目录；不能是private_path的上级（public看到的不能比private多，要么看到的就不一样）。',
            'sitename' => '网站的名称',
            'Onedrive_ver' => 'Onedrive版本',
        ],
        'ja' => [
            'admin' => 'パスワードを管理する、追加しない場合、ログインページは表示されず、ログインできません。',
            'adminloginpage' => '設定すると、ログインボタンとページが非表示になります。ログインを管理するためのページは\'?admin \'ではなく、\'?この設定の値\'。',
            'domain_path' => '複数のカスタムドメイン名を使用する場合、各ドメイン名に表示されるディレクトリを指定します。形式はa1.com:/dirto/path1|b1.com:/path2で、private_pathよりも優先されます。',
            'guestup_path' => 'マップベッドのパスを設定します。この値が設定されていない場合、ディレクトリの内容は通常ファイルにリストされ、設定後はアップロードインターフェイスのみが表示されます。',
            'passfile' => 'カスタムパスワードファイルの名前は、\'pppppp \'、\'aaaa.txt \'などの場合があります。ディレクトリをリストするときには表示されません。パスワードを知っている場合にのみ、このファイルを表示またはダウンロードできます。 パスワードはこのファイルの内容であり、スペースまたは漢字を使用できます。',
            'public_path' => 'APIのロングリンクアクセスを使用する場合、ネットワークディスクファイルのパスが表示されますが、設定されていない場合はデフォルトでルートディレクトリになり、private_pathの上位にはなりません（publicはprivate以上のものを見ることができません。それ以外は異なります。）。',
            'sitename' => 'ウェブサイト名',
            'Onedrive_ver' => 'Onedriveバージョン',
        ],
    ],
    'SetSecretsFirst' => [
        'en-us' => 'Set API in Config first! or reinstall.',
        'zh-cn' => '先在环境变量设置API！或重装。',
        'ja' => '最初に環境変数にAPIを設定してください！',
    ],
    'RefleshtoLogin' => [
        'en-us' => '<font color="red">Reflesh</font> and login.',
        'zh-cn' => '请<font color="red">刷新</font>页面后重新登录',
        'ja' => 'ページを<font color = "red">更新</font>して、再度ログインしてください',
    ],
    'AdminLogin' => [
        'en-us' => 'Admin Login',
        'zh-cn' => '管理登录',
        'ja' => 'ログインを管理する',
    ],
    'LoginSuccess' => [
        'en-us' => 'Login Success!',
        'zh-cn' => '登录成功，正在跳转',
        'ja' => 'ログイン成功、ジャンプ',
    ],
    'InputPassword' => [
        'en-us' => 'Input Password',
        'zh-cn' => '输入密码',
        'ja' => 'パスワードを入力してください',
    ],
    'Login' => [
        'en-us' => 'Login',
        'zh-cn' => '登录',
        'ja' => 'サインイン',
    ],
    'encrypt' => [
        'en-us' => 'Encrypt',
        'zh-cn' => '加密',
        'ja' => '暗号化',
    ],
    'SetpassfileBfEncrypt' => [
        'en-us' => 'Set \'passfile\' in Environments before encrypt',
        'zh-cn' => '先在环境变量设置passfile才能加密',
        'ja' => '最初に暗号化する環境変数にパスファイルを設定します',
    ],
    'updateProgram' => [
        'en-us' => 'Update Program',
        'zh-cn' => '一键更新',
        'ja' => 'ワンクリック更新',
    ],
    'UpdateSuccess' => [
        'en-us' => 'Program update Success!',
        'zh-cn' => '程序升级成功！',
        'ja' => 'プログラムのアップグレードに成功しました！',
    ],
    'Setup' => [
        'en-us' => 'Setup',
        'zh-cn' => '设置',
        'ja' => '設定する',
    ],
    'Back' => [
        'en-us' => 'Back',
        'zh-cn' => '返回',
        'ja' => 'back',
    ],
    'NotNeedUpdate' => [
        'en-us' => 'Not Need Update',
        'zh-cn' => '不需要更新',
        'ja' => '更新不要',
    ],
    'PlatformConfig' => [
        'en-us' => 'Platform Config',
        'zh-cn' => '平台变量',
        'ja' => '',
    ],
    'DelDisk' => [
        'en-us' => 'Del This Disk',
        'zh-cn' => '删除此盘',
        'ja' => '',
    ],
    'AddDisk' => [
        'en-us' => 'Add Onedrive Disk',
        'zh-cn' => '添加Onedrive盘',
        'ja' => '',
    ],
    'Home' => [
        'en-us' => 'Home',
        'zh-cn' => '首页',
        'ja' => 'ホーム',
    ],
    'NeedUpdate' => [
        'en-us' => 'Program can update<br>Click setup in Operate at top.',
        'zh-cn' => '可以升级程序<br>在上方管理菜单中<br>进入设置页面升级',
        'ja' => 'プログラムをアップグレードできます<br>上記の管理メニューで<br>アップグレードする設定ページに入ります',
    ],
    'Operate' => [
        'en-us' => 'Operate',
        'zh-cn' => '管理',
        'ja' => '管理',
    ],
    'Logout' => [
        'en-us' => 'Logout',
        'zh-cn' => '登出',
        'ja' => 'ログアウトする',
    ],
    'Create' => [
        'en-us' => 'Create',
        'zh-cn' => '新建',
        'ja' => '新しい',
    ],
    'Download' => [
        'en-us' => 'download',
        'zh-cn' => '下载',
        'ja' => 'ダウンロードする',
    ],
    'ClicktoEdit' => [
        'en-us' => 'Click to edit',
        'zh-cn' => '点击后编辑',
        'ja' => 'クリック後に編集',
    ],
    'Save' => [
        'en-us' => 'Save',
        'zh-cn' => '保存',
        'ja' => '保存する',
    ],
    'FileNotSupport' => [
        'en-us' => 'File not support preview.',
        'zh-cn' => '文件格式不支持预览',
        'ja' => 'ファイル形式はプレビューをサポートしていません',
    ],
    'File' => [
        'en-us' => 'File',
        'zh-cn' => '文件',
        'ja' => 'ファイル',
    ],
    'ShowThumbnails' => [
        'en-us' => 'Thumbnails',
        'zh-cn' => '图片缩略',
        'ja' => '画像のサムネイル',
    ],
    'EditTime' => [
        'en-us' => 'EditTime',
        'zh-cn' => '修改时间',
        'ja' => '変更時間',
    ],
    'Size' => [
        'en-us' => 'Size',
        'zh-cn' => '大小',
        'ja' => 'サイズ ',
    ],
    'Rename' => [
        'en-us' => 'Rename',
        'zh-cn' => '重命名',
        'ja' => '名前を変更',
    ],
    'Move' => [
        'en-us' => 'Move',
        'zh-cn' => '移动',
        'ja' => '移動する',
    ],
    'CannotMove' => [
        'en-us' => 'Can not Move!',
        'zh-cn' => '不能移动！',
        'ja' => '',
    ],
    'Delete' => [
        'en-us' => 'Delete',
        'zh-cn' => '删除',
        'ja' => '削除する',
    ],
    'PrePage' => [
        'en-us' => 'PrePage',
        'zh-cn' => '上一页',
        'ja' => '前へ',
    ],
    'NextPage' => [
        'en-us' => 'NextPage',
        'zh-cn' => '下一页',
        'ja' => '次のページ',
    ],
    'Upload' => [
        'en-us' => 'Upload',
        'zh-cn' => '上传',
        'ja' => 'アップロードする',
    ],
    'FileSelected' => [
        'en-us' => 'Select File',
        'zh-cn' => '选择文件',
        'ja' => '',
    ],
    'NoFileSelected' => [
        'en-us' => 'Not Select File',
        'zh-cn' => '没有选择文件',
        'ja' => '',
    ],
    'Submit' => [
        'en-us' => 'Submit',
        'zh-cn' => '确认',
        'ja' => '確認する',
    ],
    'Close' => [
        'en-us' => 'Close',
        'zh-cn' => '关闭',
        'ja' => '閉じる',
    ],
    'InputPasswordUWant' => [
        'en-us' => 'Input Password you Want',
        'zh-cn' => '输入想要设置的密码',
        'ja' => '設定するパスワードを入力してください',
    ],
    'ParentDir' => [
        'en-us' => 'Parent Dir',
        'zh-cn' => '上一级目录',
        'ja' => '親ディレクトリ',
    ],
    'Folder' => [
        'en-us' => 'Folder',
        'zh-cn' => '文件夹',
        'ja' => 'フォルダー',
    ],
    'Name' => [
        'en-us' => 'Name',
        'zh-cn' => '名称',
        'ja' => '名前',
    ],
    'Content' => [
        'en-us' => 'Content',
        'zh-cn' => '内容',
        'ja' => '内容',
    ],
    'CancelEdit' => [
        'en-us' => 'Cancel Edit',
        'zh-cn' => '取消编辑',
        'ja' => '編集をキャンセル',
    ],
    'GetFileNameFail' => [
        'en-us' => 'Fail to Get File Name!',
        'zh-cn' => '获取文件名失败！',
        'ja' => 'ファイル名を取得できませんでした！',
    ],
    'GetUploadLink' => [
        'en-us' => 'Get Upload Link',
        'zh-cn' => '获取上传链接',
        'ja' => 'アップロードリンクを取得',
    ],
    'UpFileTooLarge' => [
        'en-us' => 'The File is too Large!',
        'zh-cn' => '文件过大，终止上传。',
        'ja' => '超えると、アップロードは終了します。',
    ],
    'UploadStart' => [
        'en-us' => 'Upload Start',
        'zh-cn' => '开始上传',
        'ja' => 'アップロードを開始',
    ],
    'UploadStartAt' => [
        'en-us' => 'Start At',
        'zh-cn' => '开始于',
        'ja' => 'で開始',
    ],
    'ThisTime' => [
        'en-us' => 'This Time',
        'zh-cn' => '本次',
        'ja' => '今回は',
    ],
    'LastUpload' => [
        'en-us' => 'Last time Upload',
        'zh-cn' => '上次上传',
        'ja' => '上回は',
    ],
    'AverageSpeed' => [
        'en-us' => 'AverageSpeed',
        'zh-cn' => '平均速度',
        'ja' => '平均速度',
    ],
    'CurrentSpeed' => [
        'en-us' => 'CurrentSpeed',
        'zh-cn' => '即时速度',
        'ja' => 'インスタントスピード',
    ],
    'Expect' => [
        'en-us' => 'Expect',
        'zh-cn' => '预计还要',
        'ja' => '期待される',
    ],
    'EndAt' => [
        'en-us' => 'End At',
        'zh-cn' => '结束于',
        'ja' => 'で終了',
    ],
    'UploadErrorUpAgain' => [
        'en-us' => 'Maybe error, do upload again.',
        'zh-cn' => '可能出错，重新上传。',
        'ja' => '間違っている可能性があります。もう一度アップロードしてください。',
    ],
    'UploadComplete' => [
        'en-us' => 'Upload Complete',
        'zh-cn' => '上传完成',
        'ja' => 'アップロード完了',
    ],
    'UploadFail23' => [
        'en-us' => 'Upload Fail, contain #.',
        'zh-cn' => '目录或文件名含有#，上传失败。',
        'ja' => 'ディレクトリまたはファイル名に＃が含まれています。アップロードに失敗しました。',
    ],
    'defaultSitename' => [
        'en-us' => 'OneManager',
        'zh-cn' => 'OneManager',
        'ja' => 'OneManager',
    ],
    'SavingToken' => [
        'en-us' => 'Saving refresh_token!',
        'zh-cn' => '正在保存 refresh_token！',
        'ja' => '',
    ],
    'MayinEnv' => [
        'en-us' => 'The \'Onedrive_ver\' may in Config',
        'zh-cn' => 'Onedrive_ver应该已经写入',
        'ja' => 'Onedrive_verは環境変数に書き込まれている必要があります',
    ],
    'Wait' => [
        'en-us' => 'Wait',
        'zh-cn' => '稍等',
        'ja' => 'ちょっと待って',
    ],
    'WaitJumpIndex' => [
        'en-us' => 'Wait 5s jump to Home page',
        'zh-cn' => '等5s跳到首页',
        'ja' => '5秒待ってホームページにジャンプします',
    ],
    'JumptoOffice' => [
        'en-us' => 'Login Office and Get a refresh_token',
        'zh-cn' => '跳转到Office，登录获取refresh_token',
        'ja' => 'Officeにジャンプしてログインし、refresh_tokenを取得します',
    ],
    'OnedriveDiskTag' => [
        'en-us' => 'Onedrive Disk Tag',
        'zh-cn' => 'Onedrive 标签',
        'ja' => '',
    ],
    'OnedriveDiskName' => [
        'en-us' => 'Onedrive Showed Name',
        'zh-cn' => 'Onedrive 显示名称',
        'ja' => '',
    ],
    'OndriveVerMS' => [
        'en-us' => 'default(Onedrive, Onedrive for business)',
        'zh-cn' => '默认（支持商业版与个人版）',
        'ja' => 'デフォルト（商用版および個人版をサポート）',
    ],
    'OndriveVerCN' => [
        'en-us' => 'Onedrive in China',
        'zh-cn' => '世纪互联版',
        'ja' => '中国のOnedrive',
    ],
    'OndriveVerMSC' =>[
        'en-us' => 'default but use customer app id & secret',
        'zh-cn' => '国际版，自己申请应用ID与机密',
        'ja' => '国際版、アプリケーションIDとシークレットを自分で申請する',
    ],
    'GetSecretIDandKEY' =>[
        'en-us' => 'Get customer app id & secret',
        'zh-cn' => '申请应用ID与机密',
        'ja' => 'アプリケーションIDとシークレット',
    ],
    'TagFormatAlert' =>[
        'en-us' => 'Tag Only letters and numbers, must start with letters, at least 2 letters!',
        'zh-cn' => '标签只能字母与数字，至少2位',
        'ja' => '',
    ],
    'ClickInstall' =>[
        'en-us' => 'Click to install the project',
        'zh-cn' => '点击开始安装程序',
        'ja' => '',
    ],
    'LogintoBind' =>[
        'en-us' => 'then login and bind your onedrive in setup',
        'zh-cn' => '然后登录后在设置中绑定你的onedrive。',
        'ja' => '',
    ],
    'MakesuerWriteable' =>[
        'en-us' => 'Plase make sure the config.php is writeable. run writeable.sh.',
        'zh-cn' => '确认config.php可写。',
        'ja' => '',
    ],
    'MakesuerRewriteOn' =>[
        'en-us' => 'Plase make sure the RewriteEngine is On.',
        'zh-cn' => '确认重写（伪静态）功能启用。',
        'ja' => '',
    ],
    
    'Reflesh' => [
        'en-us' => 'Reflesh',
        'zh-cn' => '刷新',
        'ja' => '再表示',
    ],
    'SelectLanguage' => [
        'en-us' => 'Select Language',
        'zh-cn' => '选择语言',
        'ja' => '言語を選択してください',
    ],
    'RefreshCache' => [
        'en-us' => 'RefreshCache',
        'zh-cn' => '刷新缓存',
        'ja' => 'キャッシュを再構築',
    ],
];
