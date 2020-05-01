<?php if(!empty($_POST['user'])&&!empty($_POST['pass'])&&!empty($_POST['Verification'])){
	require_once 'https://raw.githubusercontent.com/PHPGangsta/GoogleAuthenticator/master/PHPGangsta/GoogleAuthenticator.php';

	$ga = new PHPGangsta_GoogleAuthenticator();

	//"安全密匙SecretKey" 入库,和账户关系绑定,客户端也是绑定这同一个"安全密匙SecretKey"
	$secret = '1GNPUHS46J0O6R7HN';

	$oneCode = $_POST['Verification'];
	$checkResult = $ga->verifyCode($secret, $oneCode, 2);
	if($checkResult){
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		if($user=='admin'&&$pass=='admin000'){
			exit('登录成功');
		}else{
			exit('密码错误');
		}
	}else{
		exit('验证码错误');
	}}?><!DOCTYPE html><html><head>
	<title>登录</title></head><body>
	<div>
		<form action="login.php" method="post">
			<p>账号：<input type="text" name="user" placeholder="账号"></p>
			<p>密码：<input type="pass" name="pass"></p>
			<p>验证码：<input type="number" name="Verification"></p>
			<input type="submit" value="提交">
		</form>
	</div></body></html>
