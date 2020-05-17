<!DOCTYPE html>
<html lang="<?php echo $constStr['language']; ?>">
<head>
    <title><?php echo $pretitle; if ($_SERVER['base_disk_path']!=$_SERVER['base_path']) { if (getConfig('diskname')!='') $diskname = getConfig('diskname'); else $diskname = $_SERVER['disktag']; echo ' - ' . $diskname; } ?> - <?php echo $_SERVER['sitename'];?></title>
    <meta charset="utf-8">
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,initial-scale=1.0">
    <meta name="keywords" content="<?php echo $n_path;?>,<?php if ($p_path!='') echo $p_path.','; echo $_SERVER['sitename'];?>">
    <meta name="description" content="<?php if ($_GET['preview']) echo 'Preview of '.$n_path; else echo 'List of '.$n_path; ?>. Li-Share-Storage-Mini,auth_by_BingoKingo,power_by_ysun.">
    <link rel="icon" href="<?php echo $_SERVER['base_disk_path'];?>favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $_SERVER['base_disk_path'];?>favicon.ico" type="image/x-icon" />
    <style type="text/css">
        body{font-family:'-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif',Helvetica,Arial,sans-serif;font-size:14px;line-height:1em;color:#000;background-color:#f7f7f9;}
        .bkgd{<?php if (getConfig('background')) { ?>background-repeat:no-repeat;background-position:center;background-size:cover;background-attachment:fixed;background-image:url("<?php echo getConfig('background'); ?>");<?php } ?>;width:100%;height:100%;filter:blur(1px)brightness(88%);position:fixed;left:0;top:0;z-index:-1}
        a{color:#24292e;cursor:pointer;text-decoration:none}
        ion-icon{font-size:16px;vertical-align:middle}
        ::selection{background-color:rgba(200,200,200,0.6)}
        input{cursor:pointer;padding: 0 8px;height:24px;font-weight:bold;border:1px solid rgba(27,31,35,0);transition-duration: 0.3s;border-radius:16px;background-color:transparent;color:#24292e}
    	input:focus{background:rgb(3,102,214,0.6);color:#FFF;box-shadow:0 1px 15px rgba(27,31,35,.15);border:1px solid rgba(27,31,35,.15);}
        textarea{font-weight:bold;border:1px solid rgba(27,31,35,0);transition-duration: 0.3s;border-radius:6px;background-color:transparent;color:#24292e}
        textarea:focus{background:rgba(3,102,214,0.6);color:#FFF;box-shadow:0 1px 15px rgba(27,31,35,.15);border:1px solid rgba(27,31,35,.15);}
        .changelanguage{position:absolute;right:5px;}   
        .title{text-shadow:0 1px 15px rgba(27,31,35,1);text-decoration:none;transition-duration: 0.3s;border:1px solid rgba(27,31,35,0);text-align:center;letter-spacing:1px;height: 10px;margin:1rem auto;padding: 16px 16px;white-space:nowrap;overflow:hidden;width:fit-content;border-radius:36px;}
        .title:hover{text-shadow:0 1px 15px rgba(27,31,35,0);background-color:rgba(200,200,200,1);box-shadow:0 1px 15px rgba(27,31,35,.15);}
        .title a{color:#FFF}
        .list-wrapper{width:88%;margin:0 auto 10px;position:relative;box-shadow:0 1px 15px rgba(27,31,35,.15);border:1px solid rgba(27,31,35,.15);border-radius:6px;}
        .list-container{position:relative;overflow:hidden;border-radius:6px}
        .list-header-container{position:relative}
        .list-header-container a.back-link{color: #000;display: inline-block;font-size: 16px;margin: 8px 8px;padding: 8px 8px;vertical-align:middle;text-decoration: none;position: absolute;top: 4px;left: 12px}
        .list-container,.list-header-container,.list-wrapper,a.back-link:hover,body{color:#24292e}
        .table-header{margin:0;border:0 none;padding:18px 48px;line-height:1.3;text-align:left;font-weight:400;color:#556677;background-color:rgba(245,245,245,0.3);word-break: break-all;word-wrap: break-word;}
        .list-body-container{position:relative;left:0;overflow-x:hidden;overflow-y:auto;box-sizing:border-box;background:rgba(245,245,245,1)}
        .more-disk{margin:0;border:0 none;padding:18px 18px;text-align:center;font-weight:400;color:#000;background-color:rgb(245,245,245);white-space:nowrap;overflow:auto;}
        .more-disk a{border:1px solid rgba(27,31,35,0);font-weight:bold;margin:0 2px;padding:5px;transition-duration: 0.3s;border-radius: 18px;background-color:transparent;color: #24292e}
        .more-disk a:hover, .more-disk a[now]{color:#FFF;background:rgba(205,205,205);border:1px solid rgba(27,31,35,.15);box-shadow:0 1px 15px rgba(27,31,35,.15)}
        .list-table{width:100%;padding:0 20px 20px 20px;border-spacing:0;overflow:hidden;table-layout:fixed}
        .list-table tr{height:fit-content;float:left;width:100%;transition-duration: 0.3s;border-radius:16px}
        .list-table tr[data-to]:hover{background:linear-gradient(to right, rgb(3,102,214,0.9) , rgba(3,102,214,0.3));color:white;box-shadow:0 1px 15px rgba(27,31,35,.15)}
        .list-table tr[data-to]:hover a{color:white}
        .list-table tr:first-child{background:rgba(245,245,245,0)}
        .list-table td.updated_at,.list-table th.updated_at{width:15%;}
        .list-table td.file,.list-table th.file{width:70%;}
        .list-table td.size,.list-table th.size{width:10%;}
        .list-table td,.list-table th{padding:0 8px;text-align:left;float:left;line-height:32px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .list-table td button,.list-table th button,.swal-button{border-radius:16px;transition-duration: 0.3s;cursor:pointer;color:#24292e;height:24px;padding: 0 8px;background:transparent;border:1px solid rgba(27,31,35,0);font-weight:bold;}
        .list-table td button:hover,.list-table th button:hover,.swal-button:hover{color:#FFF;background:rgba(3,102,214,0.6);border:1px solid rgba(27,31,35,.15);box-shadow:0 1px 15px rgba(27,31,35,.15);}
        .list-table .size,.list-table .updated_at{text-align:right}
        .mask{position:absolute;left:0px;top:0px;width:100%;background-color:#000;filter:alpha(opacity=30);opacity:0.3;z-index:2;}
<?php if ($_SERVER['admin']) { ?>
        .operate{display:inline-table;margin:0;margin-right:5px;list-style:none}
        .operate ul{position:absolute;display:none;background:white;transition-duration: 0.3s;box-shadow:0 1px 15px rgba(27,31,35,.15);border:1px solid rgba(27,31,35,.15);border-radius:32px;margin:-7px 0 0 0;padding:0 3px;color:#205D67;z-index:3;}
        .operate:hover ul{position:absolute;display:inline-table;}
        .operate ul li{padding:6px;list-style:none;display:block;float:left;font-size:0px;line-height:16px;}
        .list-table tr[data-to]:hover .operate ul li a{color:#24292e}
<?php } ?>
        .operatediv{position:absolute;border:0px #CCCCCC;box-shadow:0 1px 15px rgba(27,31,35,.15);border:1px solid rgba(27,31,35,.15);transition-duration: 0.3s;border-radius:6px;background-color:white;color:#24292e;z-index:3;}
        .operatediv div{margin:16px}
        .operatediv_close{position:absolute;right:3px;top:3px;}
        .readme{padding:8px;background-color:rgb(245,245,245);}
        .markdown-body{padding:20px;text-align:left;font-size:14px !important}
        @media only screen and (max-width:1152px){
            .list-table td.file,.list-table th.file{width:calc(95% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:925px){
            .list-table td.file,.list-table th.file{width:calc(94% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:781px){
            .list-table td.file,.list-table th.file{width:calc(93% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:679px){
            .list-table td.file,.list-table th.file{width:calc(92% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:605px){
            .list-table td.file,.list-table th.file{width:calc(91% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:542px){
            .list-table td.file,.list-table th.file{width:calc(90% - 220px)}
            .list-table td.size,.list-table th.size{width:70px !important}
            .list-table td.updated_at,.list-table th.updated_at{width:140px !important}
        }
        @media only screen and (max-width:495px){
            .title{margin-bottom:24px}
            .list-wrapper{width:96%; margin:0 auto 10px;}
            .list-table {padding:8px}
            .list-table td.file,.list-table th.file{width:95%;}
            .size, .updated_at{display:none}
            .list-table td,.list-table th{overflow:auto;text-overflow:unset}
        }
    </style>
</head>
<body>
<?php 
echo '<script language="javascript">';
?>
<?php 
echo 'window.onload=function(){var colorList=["linear-gradient(to right,rgba(169,221,204,0.9),rgba(169,221,204,0.3))","linear-gradient(to right,rgba(255,104,103,0.9),rgba(255,104,103,0.3))","linear-gradient(to right,rgba(128,155,156,0.9),rgba(128,155,156,0.3))","linear-gradient(to right,rgba(0,204,203,0.9),rgba(0,204,203,0.3))","linear-gradient(to right,rgba(246,197,0,0.9),rgba(246,197,0,0.3))","linear-gradient(to right,rgba(76,218,100,0.9),rgba(76,218,100,0.3))","linear-gradient(to right,rgba(244,67,54,0.9),rgba(244,67,54,0.3))","linear-gradient(to right,rgba(233,30,99,0.9),rgba(233,30,99,0.3))","linear-gradient(to right,rgba(156,39,176,0.9),rgba(156,39,176,0.3))","linear-gradient(to right,rgba(103,58,183,0.9),rgba(103,58,183,0.3))","linear-gradient(to right,rgba(63,81,181,0.9),rgba(63,81,181,0.3))","linear-gradient(to right,rgba(33,150,243,0.9),rgba(33,150,243,0.3))","linear-gradient(to right,rgba(3,169,244,0.9),rgba(3,169,244,0.3))","linear-gradient(to right,rgba(0,188,212,0.9),rgba(0,188,212,0.3))","linear-gradient(to right,rgba(0,150,136,0.9),rgba(0,150,136,0.3))","linear-gradient(to right,rgba(76,175,80,0.9),rgba(76,175,80,0.3))","linear-gradient(to right,rgba(139,52,171,0.9),rgba(139,52,171,0.3))","linear-gradient(to right,rgba(205,220,57,0.9),rgba(205,220,57,0.3))","linear-gradient(to right,rgba(197,183,60,0.9),rgba(197,183,60,0.3))","linear-gradient(to right,rgba(228,173,8,0.9),rgba(228,173,8,0.3))","linear-gradient(to right,rgba(255,87,34,0.9),rgba(255,87,34,0.3))","linear-gradient(to right,rgba(121,85,72,0.9),rgba(121,85,72,0.3))","linear-gradient(to right,rgba(96,125,139,0.9),rgba(96,125,139,0.3))"];var div=document.getElementsByClassName("list-header-container");for(var i=0;i<div.length;i++){var bgColor=getColorByRandom(colorList);div[i].style.background=bgColor}function getColorByRandom(colorList){var colorIndex=Math.floor(Math.random()*colorList.length);var color=colorList[colorIndex];colorList.splice(colorIndex,1);return color}};';
?>
<?php 
echo '</script>';
?>
    <div class="bkgd"></div>
    <div style="padding:1px">
<?php
    if (getConfig('admin')!='') if (!$_SERVER['admin']) {
        if (getConfig('adminloginpage')=='') { ?>
        <a class="login" onclick="login();"><ion-icon name="log-in"></ion-icon><?php echo getconstStr('Login'); ?></a>
<?php   }
    } else { ?>
        <li class="operate"><ion-icon name="construct"></ion-icon><?php echo getconstStr('Operate'); ?><ul>
<?php   if (isset($files['folder'])) { ?>
            <li><a onclick="showdiv(event,'create','');"><ion-icon name="add-circle"></ion-icon><?php echo getconstStr('Create'); ?></a></li>
            <li><a onclick="showdiv(event,'encrypt','');"><ion-icon name="lock"></ion-icon><?php echo getconstStr('encrypt'); ?></a></li>
            <li><a href="?RefreshCache"><ion-icon name="refresh"></ion-icon><?php echo getconstStr('RefreshCache'); ?></a></li>
<?php   } ?>
            <li><a href="<?php echo isset($_GET['preview'])?'?preview&':'?';?>setup"><ion-icon name="settings"></ion-icon><?php echo getconstStr('Setup'); ?></a></li>
            <li><a onclick="logout()"><ion-icon name="log-out"></ion-icon><?php echo getconstStr('Logout'); ?></a></li>
        </ul></li>
<?php
    } ?>
        &nbsp;
        <select class="changelanguage" name="language" onchange="changelanguage(this.options[this.options.selectedIndex].value)"  style="display:none !important">
            <option value="">Language</option>
<?php
    foreach ($constStr['languages'] as $key1 => $value1) { ?>
            <option value="<?php echo $key1; ?>" <?php echo $key1==$constStr['language']?'selected="selected"':'' ?>><?php echo $value1; ?></option>
<?php
    } ?>
        </select>
    </div>
<?php
    if (isset($_SERVER['needUpdate'])&&$_SERVER['needUpdate']) { ?>
    <div style='position:absolute;'><font color='red'><?php echo getconstStr('NeedUpdate'); ?></font></div>
<?php } ?>
    <h1 class="title">
	<a href="<?php echo $_SERVER['base_path']; ?>"><?php echo $_SERVER['sitename']; ?></a>
    </h1>
<?php $disktags = explode("|",getConfig('disktag'));
    if (count($disktags)>1) { ?>
    <div class="list-wrapper">
        <div class="list-container">
            <div class="list-header-container">
                <div class="more-disk">
<?php foreach ($disktags as $disk) {
        $diskname = getConfig('diskname', $disk);
        if ($diskname=='') $diskname = $disk;
        echo '                    <a href="'.path_format($_SERVER['base_path'].'/'.$disk.'/').'"'.($_SERVER['disktag']==$disk?' now':'').'>'.$diskname.'</a>
';
    } ?>
                </div>
            </div>
        </div>
    </div>
<?php }
    if ($files) {
        if (isset($files['children']['head.md'])) { ?>
    <div class="list-wrapper" id="head-div">
        <div class="list-container">
            <div class="list-body-container">
                <div class="readme">
                    <div class="markdown-body" id="head">
                        <textarea id="head-md" style="display:none;"><?php echo fetch_files(spurlencode(path_format($path . '/head.md'),'/'))['content']['body']; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php   } ?>
    <div class="list-wrapper" id="list-div">
        <div class="list-container">
            <div class="list-header-container" style="box-shadow:0 1px 8px rgba(27,31,35,.3)">
<?php
    if ($path !== '/') {
        $current_url = $_SERVER['PHP_SELF'];
        while (substr($current_url, -1) === '/') {
            $current_url = substr($current_url, 0, -1);
        }
        if (strpos($current_url, '/') !== FALSE) {
            $parent_url = substr($current_url, 0, strrpos($current_url, '/'));
        } else {
            $parent_url = $current_url;
        }
?>
                <a href="<?php echo $parent_url.'/'; ?>" class="back-link">
                    <ion-icon name="arrow-back"></ion-icon>
                </a>
<?php } ?>
                <h3 class="table-header"><?php echo str_replace('%23', '#', str_replace('&','&amp;', $path)); ?></h3>
            </div>
            <div class="list-body-container">
<?php
    $pdfurl = false;
    $DPvideo = false;
    if ($_SERVER['is_guestup_path']&&!$_SERVER['admin']) { ?>
                <div id="upload_div" style="margin:10px">
                <center>
                    <input id="upload_file" type="file" name="upload_filename">
                    <input id="upload_submit" onclick="preup();" value="<?php echo getconstStr('Upload'); ?>" type="button">
                <center>
                </div>
<?php } else {
        if ($_SERVER['ishidden']<4) {
            if (isset($files['error'])) {
                    echo '<div style="margin:8px;">' . $files['error']['message'] . '</div>';
                    $statusCode=404;
            } else {
                if (isset($files['file'])) {
?>
                <div style="margin: 12px 4px 4px; text-align: center">
                    <div style="margin: 24px">
                        <textarea id="url" title="url" rows="1" style="width: 100%; margin-top: 2px;" readonly><?php echo str_replace('%2523', '%23', str_replace('%26amp%3B','&amp;',spurlencode(path_format($_SERVER['base_disk_path'] . '/' . $path), '/'))); ?></textarea>
                        <a href="<?php echo path_format($_SERVER['base_disk_path'] . '/' . $path);//$files[$_SERVER['DownurlStrName']] ?>"><ion-icon name="download" style="line-height: 16px;vertical-align: middle;"></ion-icon>&nbsp;<?php echo getconstStr('Download'); ?></a>
                    </div>
                    <div style="margin: 24px">
<?php               $ext = strtolower(substr($path, strrpos($path, '.') + 1));
                    if (in_array($ext, $exts['img'])) {
                        echo '                        <img src="' . $files[$_SERVER['DownurlStrName']] . '" alt="' . substr($path, strrpos($path, '/')) . '" onload="if(this.offsetWidth>document.getElementById(\'url\').offsetWidth) this.style.width=\'100%\';" />
';
                    } elseif (in_array($ext, $exts['video'])) {
                    //echo '<video src="' . $files[$_SERVER['DownurlStrName']] . '" controls="controls" style="width: 100%"></video>';
                        $DPvideo=$files[$_SERVER['DownurlStrName']];
                        echo '                        <div id="video-a0"></div>
';
                    } elseif (in_array($ext, $exts['music'])) {
                        echo '                        <audio src="' . $files[$_SERVER['DownurlStrName']] . '" controls="controls" style="width: 100%"></audio>
';
                    } elseif (in_array($ext, ['pdf'])) {
                        /*echo '
                        <embed src="' . $files[$_SERVER['DownurlStrName']] . '" type="application/pdf" width="100%" height=800px">
';*/
                        $pdfurl = $files[$_SERVER['DownurlStrName']];
                        echo '                        <div id="pdf-d"></div>
';
                    } elseif (in_array($ext, $exts['office'])) {
                        echo '                        <iframe id="office-a" src="https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($files[$_SERVER['DownurlStrName']]) . '" style="width: 100%;height: 800px" frameborder="0"></iframe>
';
                    } elseif (in_array($ext, $exts['txt'])) {
                        $txtstr = htmlspecialchars(curl_request($files[$_SERVER['DownurlStrName']])['body']);
?>
                        <div id="txt">
<?php                   if ($_SERVER['admin']) { ?>
                        <form id="txt-form" action="" method="POST">
                            <a onclick="document.getElementById('txt-a').readOnly='';document.getElementById('txt-save').style.display='';document.getElementById('txt-editbutton').style.display='none';document.getElementById('txt-cancelbutton').style.display='';" id="txt-editbutton"><ion-icon name="create"></ion-icon><?php echo getconstStr('ClicktoEdit'); ?></a>
                            <a onclick="document.getElementById('txt-a').readOnly='readonly';document.getElementById('txt-save').style.display='none';document.getElementById('txt-editbutton').style.display='';document.getElementById('txt-cancelbutton').style.display='none';" id="txt-cancelbutton" style="display:none"><ion-icon name="close"></ion-icon><?php echo getconstStr('CancelEdit'); ?></a>&nbsp;&nbsp;&nbsp;
                            <a id="txt-save" style="display:none"><ion-icon name="save"></ion-icon><?php echo getconstStr('Save'); ?></a>
<?php                   } ?>
                            <textarea id="txt-a" name="editfile" readonly style="width: 100%; margin-top: 2px;" <?php if ($_SERVER['admin']) echo 'onchange="document.getElementById(\'txt-save\').onclick=function(){document.getElementById(\'txt-form\').submit();}"';?> ><?php echo $txtstr;?></textarea>
<?php                   if ($_SERVER['admin']) echo '</form>'; ?>
                        </div>
<?php               } /*elseif (in_array($ext, ['md'])) {
                        echo '                        <div class="markdown-body" id="readme">
                            <textarea id="readme-md" style="display:none;">' . curl_request($files[$_SERVER['DownurlStrName']])['body'] . '</textarea>
                        </div>
';
                    }*/ else {
                        echo '<span>'.getconstStr('FileNotSupport').'</span>';
                    } ?>
                    </div>
                </div>
<?php           } elseif (isset($files['folder'])) {
                    if (isset($_POST['filenum'])) $filenum = $_POST['filenum'];
                    if (!isset($filenum) and isset($files['folder']['page'])) $filenum = ($files['folder']['page']-1)*200;
                    else $filenum = 0; ?>
                <table class="list-table" id="list-table">
                    <tr id="tr0">
                        <th class="file"><a onclick="sortby('a');"><?php echo getconstStr('File'); ?></a><?php if (!(isset($_SERVER['USER'])&&$_SERVER['USER']=='qcloud')) { ?>&nbsp;&nbsp;&nbsp;<button onclick="showthumbnails(this);"><?php echo getconstStr('ShowThumbnails'); ?></button><?php } ?>&nbsp;<button onclick="CopyAllDownloadUrl('.download');"><?php echo getconstStr('CopyAllDownloadUrl'); ?></button></th>
                        <th class="updated_at"><a onclick="sortby('time');"><?php echo getconstStr('EditTime'); ?></a></th>
                        <th class="size"><a onclick="sortby('size');"><?php echo getconstStr('Size'); ?></a></th>
                    </tr>
                    <!-- Dirs -->
<?php               //echo json_encode($files['children'], JSON_PRETTY_PRINT);
                    foreach ($files['children'] as $file) {
                        // Folders
                        if (isset($file['folder'])) { 
                            $filenum++; ?>
                    <tr data-to id="tr<?php echo $filenum;?>">
                        <td class="file">
<?php                       if ($_SERVER['admin']) { ?>
                            <li class="operate"><ion-icon name="construct"></ion-icon><a><?php echo getconstStr('Operate'); ?></a>
                            <ul>
                                <li><a onclick="showdiv(event,'encrypt',<?php echo $filenum;?>);"><ion-icon name="lock"></ion-icon><?php echo getconstStr('encrypt'); ?></a></li>
                                <li><a onclick="showdiv(event, 'rename',<?php echo $filenum;?>);"><ion-icon name="create"></ion-icon><?php echo getconstStr('Rename'); ?></a></li>
                                <li><a onclick="showdiv(event, 'move',<?php echo $filenum;?>);"><ion-icon name="move"></ion-icon><?php echo getconstStr('Move'); ?></a></li>
                                <li><a onclick="showdiv(event, 'copy',<?php echo $filenum;?>);"><ion-icon name="copy"></ion-icon><?php echo getconstStr('Copy'); ?></a></li>
                                <li><a onclick="showdiv(event, 'delete',<?php echo $filenum;?>);"><ion-icon name="trash"></ion-icon><?php echo getconstStr('Delete'); ?></a></li>
                            </ul>
                            </li>
<?php                       } ?>
                            <ion-icon name="folder"></ion-icon>
                            <a id="file_a<?php echo $filenum;?>" name="filelist" href="<?php echo path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name']) . '/'); ?>"><?php echo str_replace('&','&amp;', $file['name']);?></a>
                        </td>
                        <td class="updated_at" id="folder_time<?php echo $filenum;?>"><?php echo time_format($file['lastModifiedDateTime']); ?></td>
                        <td class="size" id="folder_size<?php echo $filenum;?>"><?php echo size_format($file['size']); ?></td>
                    </tr>
<?php                   }
                    }
                    // if ($filenum) echo '<tr data-to></tr>';
                    foreach ($files['children'] as $file) {
                        // Files
                        if (isset($file['file'])) {
                            if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                                $filenum++; ?>
                    <tr data-to id="tr<?php echo $filenum;?>">
                        <td class="file">
<?php                           if ($_SERVER['admin']) { ?>
                            <li class="operate"><ion-icon name="construct"></ion-icon><a><?php echo getconstStr('Operate'); ?></a>
                            <ul>
                                <li><a onclick="showdiv(event, 'rename',<?php echo $filenum;?>);"><ion-icon name="create"></ion-icon><?php echo getconstStr('Rename'); ?></a></li>
                                <li><a onclick="showdiv(event, 'move',<?php echo $filenum;?>);"><ion-icon name="move"></ion-icon><?php echo getconstStr('Move'); ?></a></li>
                                <li><a onclick="showdiv(event, 'copy',<?php echo $filenum;?>);"><ion-icon name="copy"></ion-icon><?php echo getconstStr('Copy'); ?></a></li>
                                <li><a onclick="showdiv(event, 'delete',<?php echo $filenum;?>);"><ion-icon name="trash"></ion-icon><?php echo getconstStr('Delete'); ?></a></li>
                            </ul>
                            </li>
<?php                           }
                                $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
                                if (in_array($ext, $exts['music'])) { ?>
                            <ion-icon name="musical-notes"></ion-icon>
<?php                           } elseif (in_array($ext, $exts['video'])) { ?>
                            <ion-icon name="logo-youtube"></ion-icon>
<?php                           } elseif (in_array($ext, $exts['img'])) { ?>
                            <ion-icon name="image"></ion-icon>
<?php                           } elseif (in_array($ext, $exts['office'])) { ?>
                            <ion-icon name="paper"></ion-icon>
<?php                           } elseif (in_array($ext, $exts['txt'])) { ?>
                            <ion-icon name="clipboard"></ion-icon>
<?php                           } elseif (in_array($ext, $exts['zip'])) { ?>
                            <ion-icon name="filing"></ion-icon>
<?php                           } elseif ($ext=='iso') { ?>
                            <ion-icon name="disc"></ion-icon>
<?php                           } elseif ($ext=='apk') { ?>
                            <ion-icon name="logo-android"></ion-icon>
<?php                           } elseif ($ext=='exe') { ?>
                            <ion-icon name="logo-windows"></ion-icon>
<?php                           } else { ?>
                            <ion-icon name="document"></ion-icon>
<?php                           } ?>
                            <a id="file_a<?php echo $filenum;?>" name="filelist" href="<?php echo path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name'])); ?>?preview" target=_blank><?php echo str_replace('&','&amp;', $file['name']); ?></a>
                            <a class="download" href="<?php echo path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . str_replace('&','&amp;', $file['name']));?>"><ion-icon name="download"></ion-icon></a>
                        </td>
                        <td class="updated_at" id="file_time<?php echo $filenum;?>"><?php echo time_format($file['lastModifiedDateTime']); ?></td>
                        <td class="size" id="file_size<?php echo $filenum;?>"><?php echo size_format($file['size']); ?></td>
                    </tr>
<?php                       }
                        }
                    } ?>
                </table>
<?php               if ($files['folder']['childCount']>200) {
                        $pagenum = $files['folder']['page'];
                        $maxpage = ceil($files['folder']['childCount']/200);
                        $prepagenext = '
                <form action="" method="POST" id="nextpageform">
                    <input type="hidden" id="pagenum" name="pagenum" value="'. $pagenum .'">
                    <table width=100% border=0>
                        <tr>
                            <td width=60px align=center>';
                        if ($pagenum!=1) {
                            $prepagenum = $pagenum-1;
                            $prepagenext .= '
                                <a onclick="nextpage('.$prepagenum.');">'.getconstStr('PrePage').'</a>';
                        }
                        $prepagenext .= '
                            </td>
                            <td class="updated_at">';
                        for ($page=1;$page<=$maxpage;$page++) {
                            if ($page == $pagenum) {
                                $prepagenext .= '
                                <font color=red>' . $page . '</font> ';
                            } else {
                                $prepagenext .= '
                                <a onclick="nextpage('.$page.');">' . $page . '</a> ';
                            }
                        }
                        $prepagenext = substr($prepagenext,0,-1);
                        $prepagenext .= '
                            </td>
                            <td width=60px align=center>';
                        if ($pagenum!=$maxpage) {
                            $nextpagenum = $pagenum+1;
                            $prepagenext .= '
                                <a onclick="nextpage('.$nextpagenum.');">'.getconstStr('NextPage').'</a>';
                        }
                        $prepagenext .= '
                            </td>
                        </tr>
                    </table>
                </form>';
                        echo $prepagenext;
                    }
                    if ($_SERVER['admin']) { ?>
                <div id="upload_div" style="margin:0 0 16px 0">
                <center>
                    <input style="width:60%" id="upload_file" type="file" name="upload_filename" multiple="multiple">
                    <input id="upload_submit" onclick="preup();" value="<?php echo getconstStr('Upload'); ?>" type="button">
                </center>
                </div>
<?php               }
                } else {
                    $statusCode=500;
                    echo 'Unknown path or file.';
                    echo json_encode($files, JSON_PRETTY_PRINT);
                }
                if (isset($files['children']['readme.md'])) {
                    echo '
            </div>
        </div>
    </div>
    <div class="list-wrapper">
        <div class="list-container">
            <div class="list-body-container">
                <div class="readme">
                    <div class="markdown-body" id="readme">
                        <textarea id="readme-md" style="display:none;">' . fetch_files(spurlencode(path_format($path . '/readme.md'),'/'))['content']['body'] . '</textarea>
                    </div>
                </div>
';
                }
            }
        } else {
            echo '
                <div style="padding:20px">
	            <center>
	                <form action="" method="post">
		            <input style="width: 60%" name="password1" type="password" placeholder="'.getconstStr('InputPassword').'">
		            <input type="submit" value="'.getconstStr('Submit').'">
	                </form>
                </center>
                </div>';
            $statusCode = 401;
        }
    } ?>
            </div>
        </div>
    </div>
<?php } ?>
    <div id="mask" class="mask" style="display:none;"></div>
<?php
    if ($_SERVER['admin']) {
        if (!isset($_GET['preview'])) { ?>
    <div style="word-break: break-all;word-wrap: break-word;">
        <div id="rename_div" class="operatediv" style="display:none">
            <div>
                <label id="rename_label"></label><br><br><a onclick="operatediv_close('rename')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <form id="rename_form" onsubmit="return submit_operate('rename');">
                <input id="rename_sid" name="rename_sid" type="hidden" value="">
                <input id="rename_hidden" name="rename_oldname" type="hidden" value="">
                <input id="rename_input" name="rename_newname" type="text" value="">
                <input name="operate_action" type="submit" value="<?php echo getconstStr('Rename'); ?>">
                </form>
            </div>
        </div>
        <div id="delete_div" class="operatediv" style="display:none">
            <div>
                <br><a onclick="operatediv_close('delete')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <label id="delete_label"></label>
                <form id="delete_form" onsubmit="return submit_operate('delete');">
                <label id="delete_input"><?php echo getconstStr('Delete'); ?>?</label>
                <input id="delete_sid" name="delete_sid" type="hidden" value="">
                <input id="delete_hidden" name="delete_name" type="hidden" value="">
                <input name="operate_action" type="submit" value="<?php echo getconstStr('Submit'); ?>">
                </form>
            </div>
        </div>
        <div id="encrypt_div" class="operatediv" style="display:none">
            <div>
                <label id="encrypt_label"></label><br><br><a onclick="operatediv_close('encrypt')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <form id="encrypt_form" onsubmit="return submit_operate('encrypt');">
                <input id="encrypt_sid" name="encrypt_sid" type="hidden" value="">
                <input id="encrypt_hidden" name="encrypt_folder" type="hidden" value="">
                <input id="encrypt_input" name="encrypt_newpass" type="text" value="" placeholder="<?php echo getconstStr('InputPasswordUWant'); ?>">
                <?php if (getConfig('passfile')!='') {?><input name="operate_action" type="submit" value="<?php echo getconstStr('encrypt'); ?>"><?php } else { ?><br><label><?php echo getconstStr('SetpassfileBfEncrypt'); ?></label><?php } ?>
                </form>
            </div>
        </div>
        <div id="copy_div" class="operatediv" style="display:none">
            <div>
                <label id="copy_label"></label><br><br><a onclick="operatediv_close('copy')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <form id="copy_form" onsubmit="return submit_operate('copy');">
                <input id="copy_sid" name="copy_sid" type="hidden" value="">
                <input id="copy_hidden" name="copy_name" type="hidden" value="">
                <input id="copy_input" name="copy_input" type="hidden" value="">
                <input name="operate_action" type="submit" value="<?php echo getconstStr('Copy'); ?>">
                </form>
            </div>
        </div>
        <div id="move_div" class="operatediv" style="display:none">
            <div>
                <label id="move_label"></label><br><br><a onclick="operatediv_close('move')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <form id="move_form" onsubmit="return submit_operate('move');">
                <input id="move_sid" name="move_sid" type="hidden" value="">
                <input id="move_hidden" name="move_name" type="hidden" value="">
                <select id="move_input" name="move_folder">
<?php   if ($path != '/') { ?>
                    <option value="/../"><?php echo getconstStr('ParentDir'); ?></option>
<?php   }
        if (isset($files['children'])) foreach ($files['children'] as $file) {
            if (isset($file['folder'])) { ?>
                    <option value="<?php echo str_replace('&','&amp;', $file['name']);?>"><?php echo str_replace('&','&amp;', $file['name']);?></option>
<?php       }
        } ?>
                </select>
                <input name="operate_action" type="submit" value="<?php echo getconstStr('Move'); ?>">
                </form>
            </div>
        </div>
        <div id="create_div" class="operatediv" style="display:none">
            <div>
                <a onclick="operatediv_close('create')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
                <form id="create_form" onsubmit="return submit_operate('create');">
                    <input id="create_sid" name="create_sid" type="hidden" value="">
                    <input id="create_hidden" type="hidden" value="">
                    <table>
                        <tr>
                            <td></td>
                            <td><label id="create_label"></label></td>
                        </tr>
                        <tr>
                            <td>ããã</td>
                            <td>
                                <label><input id="create_type_folder" name="create_type" type="radio" value="folder" onclick="document.getElementById('create_text_div').style.display='none';"><?php echo getconstStr('Folder'); ?></label>
                                <label><input id="create_type_file" name="create_type" type="radio" value="file" onclick="document.getElementById('create_text_div').style.display='';" checked><?php echo getconstStr('File'); ?></label>
                            <td>
                        </tr>
                        <tr>
                        <td><?php echo getconstStr('Name'); ?>：</td>
                            <td><input style="width: 94%" id="create_input" name="create_name" type="text" value=""></td>
                        </tr>
                        <tr id="create_text_div">
                            <td><?php echo getconstStr('Content'); ?>：</td>
                            <td><textarea id="create_text" name="create_text" rows="6" cols="40"></textarea></td>
                        </tr>
                        <tr>
                            <td>　　　</td>
                            <td><input name="operate_action" type="submit" value="<?php echo getconstStr('Create'); ?>"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
<?php   }
    } else {
        if (getConfig('admin')!='') if (getConfig('adminloginpage')=='') { ?>
    <div id="login_div" class="operatediv" style="display:none">
        <div style="margin:50px">
            <a onclick="operatediv_close('login')" class="operatediv_close"><?php echo getconstStr('Close'); ?></a>
	        <center>
	            <form action="<?php echo isset($_GET['preview'])?'?preview&':'?';?>admin" method="post">
		        <input id="login_input" name="password1" type="password" placeholder="<?php echo getconstStr('InputPassword'); ?>">
		        <input type="submit" value="<?php echo getconstStr('Login'); ?>">
	            </form>
            </center>
        </div>
	</div>
<?php   }
    } ?>
    <div style="color: rgba(247,247,249,1);text-align:center;text-shadow:0 1px 15px rgba(27,31,35,1);font-weight:bold">Power by<a style="color:rgb(3,102,214)" href="https://github.com/BingoKingo/Tfo">Tfo</a>'s Theme for <a style="color:rgb(3,102,214)" href="https://github.com/qkqpttgf/OneManager-php">OneManager-php</a></div>
    <div style="color: rgba(247,247,249,0);text-align:center;text-shadow:0 1px 15px rgba(27,31,35,0);font-weight:bold;margin-top:6px"><?php echo date("Y-m-d H:i:s")." ".getconstStr('Week')[date("w")]." ".$_SERVER['REMOTE_ADDR'];?></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert@2.1.2/dist/sweetalert.min.js"></script>
</body>
<?php if ($files) { ?>
<?php if (isset($files['children']['head.md'])||isset($files['children']['readme.md'])) { ?><link rel="stylesheet" href="//unpkg.zhimg.com/github-markdown-css@3.0.1/github-markdown.css">
<script type="text/javascript" src="//unpkg.zhimg.com/marked@0.6.2/marked.min.js"></script><?php } ?>
<?php if (isset($files['folder']) && $_SERVER['is_guestup_path'] && !$_SERVER['admin']) { ?><script type="text/javascript" src="//cdn.bootcss.com/spark-md5/3.0.0/spark-md5.min.js"></script><?php } ?>
<?php if ($pdfurl!='') { ?><script src="//cdn.bootcss.com/pdf.js/2.3.200/pdf.min.js"></script><?php } ?>
<?php } ?>
<script type="text/javascript">
    function changelanguage(str)
    {
        if (str=='Language') str = '';
        document.cookie='language='+str+'; path=/';
        location.href = location.href;
    }
<?php if ($files) { ?>
    var root = '<?php echo $_SERVER["base_disk_path"]; ?>';
    function path_format(path) {
        path = '/' + path + '/';
        while (path.indexOf('//') !== -1) {
            path = path.replace('//', '/')
        }
        return path
    }
    document.querySelectorAll('.table-header').forEach(function (e) {
        var path = e.innerText;
        var paths = path.split('/');
        if (paths <= 2) return;
        e.innerHTML = '/ ';
        for (var i = 1; i < paths.length - 1; i++) {
            var to = path_format(root + paths.slice(0, i + 1).join('/'));
            e.innerHTML += '<a href="' + to + '">' + paths[i] + '</a> / '
        }
        e.innerHTML += paths[paths.length - 1];
        e.innerHTML = e.innerHTML.replace(/\s\/\s$/, '')
    });
<?php
    if (isset($_GET['preview'])) { //is preview mode. 在预览时处理 ?>
    var $url = document.getElementById('url');
    if ($url) {
        $url.innerHTML = location.protocol + '//' + location.host + $url.innerHTML;
        $url.style.height = $url.scrollHeight + 'px';
    }
    var $officearea=document.getElementById('office-a');
    if ($officearea) {
        $officearea.style.height = window.innerHeight + 'px';
    }
    var $textarea=document.getElementById('txt-a');
    if ($textarea) {
        $textarea.style.height = $textarea.scrollHeight + 'px';
    }
<?php   if (!!$DPvideo) { ?>
    function loadResources(type, src, callback) {
        let script = document.createElement(type);
        let loaded = false;
        if (typeof callback === 'function') {
            script.onload = script.onreadystatechange = () => {
                if (!loaded && (!script.readyState || /loaded|complete/.test(script.readyState))) {
                    script.onload = script.onreadystatechange = null;
                    loaded = true;
                    callback();
                }
            }
        }
        if (type === 'link') {
            script.href = src;
            script.rel = 'stylesheet';
        } else {
            script.src = src;
        }
        document.getElementsByTagName('head')[0].appendChild(script);
    }
    function addVideos(videos) {
        let host = 'https://s0.pstatp.com/cdn/expire-1-M';
        let unloadedResourceCount = 4;
        let callback = (() => {
            return () => {
                if (!--unloadedResourceCount) {
                    createDplayers(videos);
                }
            };
        })(unloadedResourceCount, videos);
        loadResources(
            'link',
            host + '/dplayer/1.25.0/DPlayer.min.css',
            callback
        );
        loadResources(
            'script',
            host + '/dplayer/1.25.0/DPlayer.min.js',
            callback
        );
        loadResources(
            'script',
            host + '/hls.js/0.12.4/hls.light.min.js',
            callback
        );
        loadResources(
            'script',
            host + '/flv.js/1.5.0/flv.min.js',
            callback
        );
    }
    function createDplayers(videos) {
        for (i = 0; i < videos.length; i++) {
            console.log(videos[i]);
            new DPlayer({
                container: document.getElementById('video-a' + i),
                screenshot: true,
                video: {
                    url: videos[i]
                }
            });
        }
    }
    addVideos(['<?php echo $DPvideo;?>']);
<?php   }
        if ($pdfurl!='') { ?>
    pdfjsLib.GlobalWorkerOptions.workerSrc = '//cdn.bootcss.com/pdf.js/2.3.200/pdf.worker.min.js';
    var loadingTask = pdfjsLib.getDocument({ url: '<?php echo $pdfurl;?>', });
    loadingTask.promise.then(function(pdf) {
        var pagenum =  pdf.numPages;
        var pdfContainer = document.getElementById('pdf-d');
        for (var i=1;i<=pagenum;i++) {
            var canvasNew = document.createElement('canvas');
            canvasNew.id = 'pdf-c'+i;
            pdfContainer.appendChild(canvasNew);
            renderpage(pdf,i);
        }
    });
    function renderpage(pdf,i)
    {
        pdf.getPage(i).then(function(page) { 
            var scale = 1.5;
            var viewport = page.getViewport({ scale: scale, });
            var canvas = document.getElementById('pdf-c'+i);
            var context = canvas.getContext("2d");
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            var renderContext = {
                canvasContext: context,
                viewport: viewport,
            };
            page.render(renderContext);
        });
    }
<?php   }
    } else { // view folder. 不预览，即浏览目录时?>
    var $head = document.getElementById('head');
    if ($head) {
        //document.getElementById('head-div').parentNode.insertBefore(document.getElementById('head-div'),document.getElementById('list-div'));
        $head.innerHTML = marked(document.getElementById('head-md').innerText);
        
    }
    var $readme = document.getElementById('readme');
    if ($readme) {
        $readme.innerHTML = marked(document.getElementById('readme-md').innerText);
    }
    function showthumbnails(obj) {
        swal("Loading Thumbnails", {
          buttons: false,
          timer: 1000,
        });
        var files=document.getElementsByName('filelist');
        for ($i=0;$i<files.length;$i++) {
            str=files[$i].innerText;
            if (str.substr(-1)==' ') str=str.substr(0,str.length-1);
            if (!str) return;
            strarry=str.split('.');
            ext=strarry[strarry.length-1].toLowerCase();
            images = [<?php foreach ($exts['img'] as $imgext) echo '\''.$imgext.'\', '; ?>];
            if (images.indexOf(ext)>-1) get_thumbnails_url(str, files[$i]);
        }
        obj.disabled='disabled';
    }
    function get_thumbnails_url(str, filea) {
        if (!str) return;
        var nurl=window.location.href;
        if (nurl.substr(-1)!="/") nurl+="/";
        var xhr = new XMLHttpRequest();
        xhr.open("GET", nurl+str+'?thumbnails', true);
                //xhr.setRequestHeader('x-requested-with','XMLHttpRequest');
        xhr.send('');
        xhr.onload = function(e){
            if (xhr.status==200) {
                if (xhr.responseText!='') filea.innerHTML='<img src="'+xhr.responseText+'" alt="'+str+'">';
            } else console.log(xhr.status+'\n'+xhr.responseText);
        }
    }
    function CopyAllDownloadUrl(str) {
        var tmptextarea=document.createElement('textarea');
        document.body.appendChild(tmptextarea);
        tmptextarea.setAttribute('style','position:absolute;left:-100px;width:0px;height:0px;');
        document.querySelectorAll(str).forEach(function (e) {
            tmptextarea.innerHTML+=e.href+"\r\n";
        });
        tmptextarea.select();
        tmptextarea.setSelectionRange(0, tmptextarea.value.length);
        document.execCommand("copy");
        swal(tmptextarea.innerHTML+'<?php echo getconstStr('Success'); ?>');
    }
    var sort=0;
    function sortby(string) {
        if (string=='a') if (sort!=0) {
            for (i = 1; i <= <?php echo $filenum?$filenum:0;?>; i++) document.getElementById('tr'+i).parentNode.insertBefore(document.getElementById('tr'+i),document.getElementById('tr'+(i-1)).nextSibling);
            sort=0;
            return;
        } else return;
        sort1=sort;
        sortby('a');
        sort=sort1;
        var a=[];
        for (i = 1; i <= <?php echo $filenum?$filenum:0;?>; i++) {
            a[i]=i;
            if (!!document.getElementById('folder_'+string+i)) {
                var td1=document.getElementById('folder_'+string+i);
                for (j = 1; j < i; j++) {
                    if (!!document.getElementById('folder_'+string+a[j])) {
                        var c=false;
                        if (string=='time') if (sort==-1) {
                            c=(td1.innerText < document.getElementById('folder_'+string+a[j]).innerText);
                        } else {
                            c=(td1.innerText > document.getElementById('folder_'+string+a[j]).innerText);
                        }
                        if (string=='size') if (sort==2) {
                            c=(size_reformat(td1.innerText) < size_reformat(document.getElementById('folder_'+string+a[j]).innerText));
                        } else {
                            c=(size_reformat(td1.innerText) > size_reformat(document.getElementById('folder_'+string+a[j]).innerText));
                        }
                        if (c) {
                            document.getElementById('tr'+i).parentNode.insertBefore(document.getElementById('tr'+i),document.getElementById('tr'+a[j]));
                            for (k = i; k > j; k--) {
                                a[k]=a[k-1];
                            }
                            a[j]=i;
                            break;
                        }
                    }
                }
            }
            if (!!document.getElementById('file_'+string+i)) {
                var td1=document.getElementById('file_'+string+i);
                for (j = 1; j < i; j++) {
                    if (!!document.getElementById('file_'+string+a[j])) {
                        var c=false;
                        if (string=='time') if (sort==-1) {
                            c=(td1.innerText < document.getElementById('file_'+string+a[j]).innerText);
                        } else {
                            c=(td1.innerText > document.getElementById('file_'+string+a[j]).innerText);
                        }
                        if (string=='size') if (sort==2) {
                            c=(size_reformat(td1.innerText) < size_reformat(document.getElementById('file_'+string+a[j]).innerText));
                        } else {
                            c=(size_reformat(td1.innerText) > size_reformat(document.getElementById('file_'+string+a[j]).innerText));
                        }
                        if (c) {
                            document.getElementById('tr'+i).parentNode.insertBefore(document.getElementById('tr'+i),document.getElementById('tr'+a[j]));
                            for (k = i; k > j; k--) {
                                a[k]=a[k-1];
                            }
                            a[j]=i;
                            break;
                        }
                    }
                }
            }
        }
        if (string=='time') if (sort==-1) {
            sort=1;
        } else {
            sort=-1;
        }
        if (string=='size') if (sort==2) {
            sort=-2;
        } else {
            sort=2;
        }
    }
    function size_reformat(str) {
        if (str.substr(-1)==' ') str=str.substr(0,str.length-1);
        if (str.substr(-2)=='GB') num=str.substr(0,str.length-3)*1024*1024*1024;
        if (str.substr(-2)=='MB') num=str.substr(0,str.length-3)*1024*1024;
        if (str.substr(-2)=='KB') num=str.substr(0,str.length-3)*1024;
        if (str.substr(-2)==' B') num=str.substr(0,str.length-2);
        return num;
    }
<?php
    }
    if ($_COOKIE['timezone']=='') { // cookie timezone. 无时区写时区 ?>
    var nowtime= new Date();
    var timezone = 0-nowtime.getTimezoneOffset()/60;
    var expd = new Date();
    expd.setTime(expd.getTime()+(2*60*60*1000));
    var expires = "expires="+expd.toGMTString();
    document.cookie="timezone="+timezone+"; path=/; "+expires;
    if (timezone!=<?php echo $_SERVER['timezone']; ?>) {
        alert('Your timezone is '+timezone+', reload local timezone.');
        location.href=location.protocol + "//" + location.host + "<?php echo path_format($_SERVER['base_path'] . '/' . $path );?>" ;
    }
<?php }
    if (isset($files['folder']['childCount'])&&$files['folder']['childCount']>200) { // more than 200. 有下一页 ?>
    function nextpage(num) {
        document.getElementById('pagenum').value=num;
        document.getElementById('nextpageform').submit();
    }
<?php }
    if (isset($files['folder']) && ($_SERVER['is_guestup_path'] || $_SERVER['admin'])) { // is folder and is admin or guest upload path. 当前是admin登录或图床目录时 ?>
    function uploadbuttonhide() {
        document.getElementById('upload_submit').disabled='disabled';
        document.getElementById('upload_file').disabled='disabled';
        document.getElementById('upload_submit').style.display='none';
        document.getElementById('upload_file').style.display='none';
    }
    function uploadbuttonshow() {
        document.getElementById('upload_file').disabled='';
        document.getElementById('upload_submit').disabled='';
        document.getElementById('upload_submit').style.display='';
        document.getElementById('upload_file').style.display='';
    }
    function preup() {
            uploadbuttonhide();
            var files=document.getElementById('upload_file').files;
        if (files.length<1) {
                uploadbuttonshow();
                return;
            };
            var table1=document.createElement('table');
            document.getElementById('upload_div').appendChild(table1);
            table1.setAttribute('class','list-table');
            var timea=new Date().getTime();
            var i=0;
            getuplink(i);
            function getuplink(i) {
                var file=files[i];
                var tr1=document.createElement('tr');
                table1.appendChild(tr1);
                tr1.setAttribute('data-to',1);
                var td1=document.createElement('td');
                tr1.appendChild(td1);
                td1.setAttribute('style','width:fit-content');
                td1.setAttribute('id','upfile_td1_'+timea+'_'+i);
                td1.innerHTML=file.name+'&nbsp;'+size_format(file.size);
                var td2=document.createElement('td');
                tr1.appendChild(td2);
                td2.setAttribute('id','upfile_td2_'+timea+'_'+i);
                td2.setAttribute('style','text-overflow:ellipsis');
                td2.setAttribute('style','white-space:nowrap');
                td2.innerHTML='<?php echo getconstStr('GetUploadLink'); ?> ...';
                if (file.size>100*1024*1024*1024) {
                    td2.innerHTML='<font color="red"><?php echo getconstStr('UpFileTooLarge'); ?></font>';
                    uploadbuttonshow();
                    return;
                }
                var xhr1 = new XMLHttpRequest();
                xhr1.open("GET", '?action=upbigfile&upbigfilename='+ encodeURIComponent(file.name) +'&filesize='+ file.size +'&lastModified='+ file.lastModified);
                xhr1.setRequestHeader('x-requested-with','XMLHttpRequest');
                xhr1.send(null);
                xhr1.onload = function(e){
                    td2.innerHTML='<font color="red">'+xhr1.responseText+'</font>';
                    if (xhr1.status==200) {
                        console.log(xhr1.responseText);
                        var html=JSON.parse(xhr1.responseText);
                        if (!html['uploadUrl']) {
                            td2.innerHTML='<font color="red">'+xhr1.responseText+'</font><br>';
                            uploadbuttonshow();
                        } else {
                            td2.innerHTML='<?php echo getconstStr('UploadStart'); ?> ...';
                            binupfile(file,html['uploadUrl'],timea+'_'+i);
                        }
                    }
                    if (i<files.length-1) {
                        i++;
                        getuplink(i);
                    }
                }
            }
        }
        function size_format(num) {
            if (num>1024) {
                num=num/1024;
            } else {
                return num.toFixed(2) + ' B';
            }
            if (num>1024) {
                num=num/1024;
            } else {
                return num.toFixed(2) + ' KB';
            }
            if (num>1024) {
                num=num/1024;
            } else {
                return num.toFixed(2) + ' MB';
            }
            return num.toFixed(2) + ' GB';
        }
        function binupfile(file,url,tdnum){
            var label=document.getElementById('upfile_td2_'+tdnum);
            var reader = new FileReader();
            var StartStr='';
            var MiddleStr='';
            var StartTime;
            var EndTime;
            var newstartsize = 0;
            if(!!file){
                var asize=0;
                var totalsize=file.size;
                var xhr2 = new XMLHttpRequest();
                xhr2.open("GET", url);
                        //xhr2.setRequestHeader('x-requested-with','XMLHttpRequest');
                xhr2.send(null);
                xhr2.onload = function(e){
                    if (xhr2.status==200) {
                        var html = JSON.parse(xhr2.responseText);
                        var a = html['nextExpectedRanges'][0];
                        newstartsize = Number( a.slice(0,a.indexOf("-")) );
                        StartTime = new Date();
    <?php if ($_SERVER['admin']) { ?>
                        asize = newstartsize;
    <?php } ?>
                        if (newstartsize==0) {
                            StartStr='<?php echo getconstStr('UploadStartAt'); ?>:' +StartTime.toLocaleString()+'<br>' ;
                        } else {
                            StartStr='<?php echo getconstStr('LastUpload'); ?>'+size_format(newstartsize)+ '&nbsp;<?php echo getconstStr('ThisTime').getconstStr('UploadStartAt'); ?>:' +StartTime.toLocaleString()+'<br>' ;
                        }
                        var chunksize=5*1024*1024; // chunk size, max 60M. 每小块上传大小，最大60M，微软建议10M
                        if (totalsize>200*1024*1024) chunksize=10*1024*1024;
                        function readblob(start) {
                            var end=start+chunksize;
                            var blob = file.slice(start,end);
                            reader.readAsArrayBuffer(blob);
                        }
                        readblob(asize);
    <?php if (!$_SERVER['admin']) { ?>
                        var spark = new SparkMD5.ArrayBuffer();
    <?php } ?>
                        reader.onload = function(e){
                            var binary = this.result;
    <?php if (!$_SERVER['admin']) { ?>
                            spark.append(binary);
                            if (asize < newstartsize) {
                                asize += chunksize;
                                readblob(asize);
                                return;
                            }
    <?php } ?>
                            var xhr = new XMLHttpRequest();
                            xhr.open("PUT", url, true);
                            //xhr.setRequestHeader('x-requested-with','XMLHttpRequest');
                            bsize=asize+e.loaded-1;
                            xhr.setRequestHeader('Content-Range', 'bytes ' + asize + '-' + bsize +'/'+ totalsize);
                            xhr.upload.onprogress = function(e){
                                if (e.lengthComputable) {
                                    var tmptime = new Date();
                                    var tmpspeed = e.loaded*1000/(tmptime.getTime()-C_starttime.getTime());
                                    var remaintime = (totalsize-asize-e.loaded)/tmpspeed;
                                    label.innerHTML=StartStr+'<?php echo getconstStr('Upload'); ?> ' +size_format(asize+e.loaded)+ ' / '+size_format(totalsize) + ' = ' + ((asize+e.loaded)*100/totalsize).toFixed(2) + '% <?php echo getconstStr('AverageSpeed'); ?>:'+size_format((asize+e.loaded-newstartsize)*1000/(tmptime.getTime()-StartTime.getTime()))+'/s<br><?php echo getconstStr('CurrentSpeed'); ?> '+size_format(tmpspeed)+'/s <?php echo getconstStr('Expect'); ?> '+remaintime.toFixed(1)+'s';
                                }
                            }
                            var C_starttime = new Date();
                            xhr.onload = function(e){
                                if (xhr.status<500) {
                                var response=JSON.parse(xhr.responseText);
                                if (response['size']>0) {
                                    // contain size, upload finish. 有size说明是最终返回，上传结束
                                    var xhr3 = new XMLHttpRequest();
                                    xhr3.open("GET", '?action=del_upload_cache&filename=.'+file.lastModified+ '_' +file.size+ '_' +encodeURIComponent(file.name)+'.tmp');
                                    xhr3.setRequestHeader('x-requested-with','XMLHttpRequest');
                                    xhr3.send(null);
                                    xhr3.onload = function(e){
                                        console.log(xhr3.responseText+','+xhr3.status);
                                    }
    <?php if (!$_SERVER['admin']) { ?>
                                    var filemd5 = spark.end();
                                    var xhr4 = new XMLHttpRequest();
                                    xhr4.open("GET", '?action=uploaded_rename&filename='+encodeURIComponent(file.name)+'&filemd5='+filemd5);
                                    xhr4.setRequestHeader('x-requested-with','XMLHttpRequest');
                                    xhr4.send(null);
                                    xhr4.onload = function(e){
                                        console.log(xhr4.responseText+','+xhr4.status);
                                        var filename;
                                        //if (xhr4.status==200) filename = JSON.parse(xhr4.responseText)['name'];
                                        //if (xhr4.status==409) filename = filemd5 + file.name.substr(file.name.indexOf('.'));
                                        filename = JSON.parse(xhr4.responseText)['name'];
                                        if (filename=='') {
                                            swal('<?php echo getconstStr('UploadErrorUpAgain'); ?>');
                                            uploadbuttonshow();
                                            return;
                                        }
                                        var lasturl = location.href;
                                        if (lasturl.substr(lasturl.length-1)!='/') lasturl += '/';
                                        lasturl += filename + '?preview';
                                        //window.open(lasturl);
                                        document.getElementById('upfile_a_'+tdnum).href = lasturl;
                                        document.getElementById('upfile_a1_'+tdnum).href = filename;
                                        document.getElementById('upfile_cpbt_'+tdnum).style.display = "";
                                    }
    <?php } ?>
                                    EndTime=new Date();
                                    MiddleStr = '<?php echo getconstStr('EndAt'); ?>:'+EndTime.toLocaleString()+'<br>';                                    
                                    if (newstartsize==0) {
                                        MiddleStr += '<?php echo getconstStr('AverageSpeed'); ?>:'+size_format(totalsize*1000/(EndTime.getTime()-StartTime.getTime()))+'/s<br>';
                                    } else {
                                        MiddleStr += '<?php echo getconstStr('ThisTime').getconstStr('AverageSpeed'); ?>:'+size_format((totalsize-newstartsize)*1000/(EndTime.getTime()-StartTime.getTime()))+'/s<br>';
                                    }
                                    document.getElementById('upfile_td1_'+tdnum).innerHTML='<div style="color:green"><a href="<?php echo $_SERVER['base_disk_path']; ?>'+response.name+'?preview" id="upfile_a_'+tdnum+'" target="_blank">'+document.getElementById('upfile_td1_'+tdnum).innerHTML+'</a>&nbsp;<a href="<?php echo $_SERVER['base_disk_path']; ?>'+response.name+'" id="upfile_a1_'+tdnum+'"></a><?php echo getconstStr('UploadComplete'); ?><button onclick="CopyAllDownloadUrl(\'#upfile_a1_'+tdnum+'\');" id="upfile_cpbt_'+tdnum+'" <?php if (!$_SERVER['admin']) echo 'style="display:none"'; ?> ><?php echo getconstStr('CopyUrl'); ?></button></div>';
                                    label.innerHTML=StartStr+MiddleStr;
                                    uploadbuttonshow();
    <?php if ($_SERVER['admin']) { ?>
                                    addelement(response);
    <?php } ?>
                                } else {
                                    if (!response['nextExpectedRanges']) {
                                        label.innerHTML='<font color="red">'+xhr.responseText+'</font>&nbsp;';
                                    } else {
                                        var a=response['nextExpectedRanges'][0];
                                        asize=Number( a.slice(0,a.indexOf("-")) );
                                        readblob(asize);
                                    }
                                } } else readblob(asize);
                            }
                            xhr.send(binary);
                        }
                    } else {
                        if (window.location.pathname.indexOf('%23')>0||file.name.indexOf('%23')>0) {
                            label.innerHTML='<font color="red"><?php echo getconstStr('UploadFail23'); ?></font>';
                        } else {
                            label.innerHTML='<font color="red">'+xhr2.responseText+'</font>';
                        }
                        uploadbuttonshow();
                    }
                }
            }
        }
<?php }
}
    if (getConfig('admin')!='') { // close div. 有登录或操作，需要关闭DIV时 ?>
    function operatediv_close(operate) {
        document.getElementById(operate+'_div').style.display='none';
        document.getElementById('mask').style.display='none';
    }
<?php }
    if ($_SERVER['admin']) { // admin login. 管理登录后 ?>
    function logout() {
        document.cookie = "admin=; path=/";
        location.href = location.href;
    }
<?php   if (!isset($_GET['preview'])) {?>
    function showdiv(event,action,num) {
        var $operatediv=document.getElementsByName('operatediv');
        for ($i=0;$i<$operatediv.length;$i++) {
            $operatediv[$i].style.display='none';
        }
        document.getElementById('mask').style.display='';
        //document.getElementById('mask').style.width=document.documentElement.scrollWidth+'px';
        document.getElementById('mask').style.height=document.documentElement.scrollHeight<window.innerHeight?window.innerHeight:document.documentElement.scrollHeight+'px';
        if (num=='') {
            var str='';
        } else {
            var str=document.getElementById('file_a'+num).innerText;
            if (str=='') {
                str=document.getElementById('file_a'+num).getElementsByTagName("img")[0].alt;
                if (str=='') {
                    alert('<?php echo getconstStr('GetFileNameFail'); ?>');
                    operatediv_close(action);
                    return;
                }
            }
            if (str.substr(-1)==' ') str=str.substr(0,str.length-1);
        }
        document.getElementById(action + '_div').style.display='';
        document.getElementById(action + '_label').innerText=str;//.replace(/&/,'&amp;');
        document.getElementById(action + '_sid').value=num;
        document.getElementById(action + '_hidden').value=str;
        if (action=='rename') document.getElementById(action + '_input').value=str;
        var $e = event || window.event;
        var $scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
        var $scrollY = document.documentElement.scrollTop || document.body.scrollTop;
        var $x = $e.pageX || $e.clientX + $scrollX;
        var $y = $e.pageY || $e.clientY + $scrollY;
        if (action=='create') {
            document.getElementById(action + '_div').style.left=(document.body.clientWidth-document.getElementById(action + '_div').offsetWidth)/2 +'px';
            document.getElementById(action + '_div').style.top=(window.innerHeight-document.getElementById(action + '_div').offsetHeight)/2+$scrollY +'px';
        } else {
            if ($x + document.getElementById(action + '_div').offsetWidth > document.body.clientWidth) {
                if (document.getElementById(action + '_div').offsetWidth > document.body.clientWidth) {
                    document.getElementById(action + '_div').offsetWidth=document.body.clientWidth+'px';
                    document.getElementById(action + '_div').style.left='0px';
                } else {
                    document.getElementById(action + '_div').style.left=document.body.clientWidth-document.getElementById(action + '_div').offsetWidth+'px';
                }
            } else {
                document.getElementById(action + '_div').style.left=$x+'px';
            }
            document.getElementById(action + '_div').style.top=$y+'px';
        }
        document.getElementById(action + '_input').focus();
    }
    function submit_operate(str) {
        var num=document.getElementById(str+'_sid').value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", '?'+serializeForm(str+'_form'));
        xhr.setRequestHeader('x-requested-with','XMLHttpRequest');
        xhr.send(null);
        xhr.onload = function(e){
            var html;
            if (xhr.status<300) {
                console.log(xhr.status+','+xhr.responseText);
                if (str=='rename') {
                    html=JSON.parse(xhr.responseText);
                    var file_a = document.getElementById('file_a'+num);
                    file_a.innerText=html.name;
                    file_a.href = (file_a.href.substr(-8)=='?preview')?(html.name.replace(/#/,'%23')+'?preview'):(html.name.replace(/#/,'%23')+'/');
                }
                if (str=='move'||str=='delete') document.getElementById('tr'+num).parentNode.removeChild(document.getElementById('tr'+num));
                if (str=='create') {
                    html=JSON.parse(xhr.responseText);
                    addelement(html);
                }
            } else swal(xhr.status+'\n'+xhr.responseText);
            document.getElementById(str+'_div').style.display='none';
            document.getElementById('mask').style.display='none';
        }
        return false;
    }
    function addelement(html) {
        var tr1=document.createElement('tr');
        tr1.setAttribute('data-to',1);
        var td1=document.createElement('td');
        td1.setAttribute('class','file');
        var a1=document.createElement('a');
        a1.href='<?php echo $_SERVER['base_disk_path'];?>'+html.name.replace(/#/,'%23');
        a1.innerText=html.name;
        a1.target='_blank';
        var td2=document.createElement('td');
        td2.setAttribute('class','updated_at');
        td2.innerText=html.lastModifiedDateTime.replace(/T/,' ').replace(/Z/,'');
        var td3=document.createElement('td');
        td3.setAttribute('class','size');
        td3.innerText=size_format(html.size);
        if (!!html.folder) {
            a1.href+='/';
            document.getElementById('tr0').parentNode.insertBefore(tr1,document.getElementById('tr0').nextSibling);
        }
        if (!!html.file) {
            a1.href+='?preview';
            a1.name='filelist';
            document.getElementById('tr0').parentNode.appendChild(tr1);
        }
        tr1.appendChild(td1);
        td1.appendChild(a1);
        tr1.appendChild(td2);
        tr1.appendChild(td3);
    }
    function getElements(formId) {
        var form = document.getElementById(formId);
        var elements = new Array();
        var tagElements = form.getElementsByTagName('input');
        for (var j = 0; j < tagElements.length; j++){
            elements.push(tagElements[j]);
        }
        var tagElements = form.getElementsByTagName('select');
        for (var j = 0; j < tagElements.length; j++){
            elements.push(tagElements[j]);
        }
        var tagElements = form.getElementsByTagName('textarea');
        for (var j = 0; j < tagElements.length; j++){
            elements.push(tagElements[j]);
        }
        return elements;
    }
    function serializeElement(element) {
        var method = element.tagName.toLowerCase();
        var parameter;
        if (method == 'select') {
            parameter = [element.name, element.value];
        }
        switch (element.type.toLowerCase()) {
            case 'submit':
            case 'hidden':
            case 'password':
            case 'text':
            case 'date':
            case 'textarea':
                parameter = [element.name, element.value];
                break;
            case 'checkbox':
            case 'radio':
                if (element.checked){
                    parameter = [element.name, element.value];
                }
                break;
        }
        if (parameter) {
            var key = encodeURIComponent(parameter[0]);
            if (key.length == 0) return;
            if (parameter[1].constructor != Array) parameter[1] = [parameter[1]];
            var values = parameter[1];
            var results = [];
            for (var i = 0; i < values.length; i++) {
                results.push(key + '=' + encodeURIComponent(values[i]));
            }
            return results.join('&');
        }
    }
    function serializeForm(formId) {
        var elements = getElements(formId);
        var queryComponents = new Array();
        for (var i = 0; i < elements.length; i++) {
            var queryComponent = serializeElement(elements[i]);
            if (queryComponent) {
                queryComponents.push(queryComponent);
            }
        }
        return queryComponents.join('&');
    }
<?php   }
    } else if (getConfig('admin')!='') if (getConfig('adminloginpage')=='') { ?>
    function login() {
        document.getElementById('mask').style.display='';
            //document.getElementById('mask').style.width=document.documentElement.scrollWidth+'px';
        document.getElementById('mask').style.height=document.documentElement.scrollHeight<window.innerHeight?window.innerHeight:document.documentElement.scrollHeight+'px';
        document.getElementById('login_div').style.display='';
        document.getElementById('login_div').style.left=(document.body.clientWidth-document.getElementById('login_div').offsetWidth)/2 +'px';
        document.getElementById('login_div').style.top=(window.innerHeight-document.getElementById('login_div').offsetHeight)/2+document.body.scrollTop +'px';
        document.getElementById('login_input').focus();
    }
<?php } ?>
</script>
<script src="//unpkg.zhimg.com/ionicons@4.4.4/dist/ionicons.js"></script>
</html>
