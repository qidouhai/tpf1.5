<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="rebots" content="noindex,nofollow" />
<meta name="Author" content="TYNT.CN" />
<title>TPF框架安装</title>
<style type="text/css">
.content {background:#FBF2D9;padding:3px;border:1px solid #F60}
.content dt {line-height:18px;font-size:16px;font-weight:bold;padding-left:12px}
.error {background:#FEE3E2;border:1px solid #f00;color:#f00;padding:9px;font-size:14px}
.error span {font-size:12px}
.content dt span {font-size:12px;color:#f00;font-weight:normal;padding:12px 0}
.copyright {font-family:Arial;text-align:center}
.copyright a {color:#009;text-decoration:none}
.content dd {padding:9px;font-size:12px}
fieldset {border:1px solid #aaa;line-height:330%}
.txt {border-left:1px solid #666;border-top:1px solid #666;border-bottom:1px solid #f1f1f1;border-right:1px solid #f1f1f1;line-height:22px;height:22px;padding-left:5px}
.button {border-left:1px solid #f1f1f1;border-top:1px solid #f1f1f1;border-bottom:1px solid #666;border-right:1px solid #666;line-height:25px;height:25px}
</style>
<script type="text/javascript">
function checkkey() {
	if(document.getElementById('key').value == '') {
		alert('请输入安装码');
		document.getElementById('key').focus();
		return false;
	}
}
function setdb(obj) {
	if(obj.checked) {
		document.getElementById('dbsetlist').style.display ='none';
	}else {
		document.getElementById('dbsetlist').style.display ='block';
	}
}
function check() {
	if(document.getElementById('nodb').checked==false) {
	if(document.getElementById('host').value == '') {
		alert('请输入数据库地址');
		document.getElementById('host').focus();
		return false;
	}
	if(document.getElementById('port').value == '') {
		alert('请输入数据库端口');
		document.getElementById('port').focus();
		return false;
	}
	if(document.getElementById('dbname').value == '') {
		alert('请输入数据库名称');
		document.getElementById('dbname').focus();
		return false;
	}
	if(document.getElementById('user').value == '') {
		alert('请输入数据库连接用户名');
		document.getElementById('user').focus();
		return false;
	}
	if(document.getElementById('pass').value == '') {
		alert('请输入数据库连接密码');
		document.getElementById('pass').focus();
		return false;
	}
	}
}
</script>
</head>
<body>
	<?php if($param['error']>0) {?>
    <div class="error">
    	<h3><?php if($param['error']==3){ echo '提示';}else{ echo '发生错误';}?></h3>
    	<?php if($param['error']==1){?>
        <?php echo APP_PATH;?>目录不可写入，请设置为可写
        <?php }elseif($param['error']==2){?> 
        安装码输入不正确,<input type="button" name="e1" id="e1" onClick="javascript:history.back()" value="返回重新填写" /><br /><span>注意：返回后安装码会重新生成，再次打开<?php echo $param['snfile'];?>查看引号内的字符串</span>
        <?php }elseif($param['error']==3){?>
        应用程序生成成功
        <?php }elseif($param['error']==4){?>
        应用程序已经生成，如需重新生成请删除应用程序根目录"<?php echo $param['lockfile'];?>"文件
        <?php }elseif($param['error']==5){?>
        在dev环境url地址中带“跟踪参数=1”可让非html头输出debug信息
        <?php }?>
    </div>
    <?php }else{?>
    <dl class="content">
    	<?php if($param['step']==1){?>
    	<dt>TPF框架安装 -- 第一步，生成安装码<br /><span>[说明：安装码的目的是为了防止多次应用安装带来的风险，点击生成安装码后会在<?php echo APP_PATH;?>目录下生成一个名为“<?php echo $param['snfile'];?>”的文件，用编辑器到打开<?php echo $param['snfile'];?>文件获取字符串，在下一步中，填写到验证文本框中]</span></dt>
    	<dd><form name="frmSetup" id="frmSetup" method="post" action="">
        <input type="hidden" name="do" id="do" value="1">
        <input type="submit" class="button" name="s1" id="s1" value="生成安装码" />
        </form></dd>
        <?php }elseif($param['step']==2){?>
        <dt>TPF框架安装 -- 第二步，验证安装码<br /><span>[说明：用编辑器打开根<?php echo APP_PATH;?>目录下生成一个名为"<?php echo $param['snfile'];?>"的文件，将引号内的字符串，填写到验证文本框中(注意大小写区分)]</span></dt>
    	<dd><form name="frmSetup" onSubmit="return checkkey()" id="frmSetup" method="post" action="">
        <input type="hidden" name="do" id="do" value="2">
        输入安装码：<input type="text" name="key" id="key" class="txt" />
        <input type="submit" class="button" name="s1" id="s1" value="下一步" />
        </form></dd>
        <?php }elseif($param['step']==3){?>
        <dt>TPF框架安装 -- 第三步，基础安装配置<br /><span></span></dt>
    	<dd><form name="frmSetup" onSubmit="return check()" id="frmSetup" method="post" action="">
        <input type="hidden" name="do" id="do" value="3">
        <fieldset>
            <legend>基本设置</legend>
            跟踪参数：<input type="text" name="tracename" id="tracename" class="txt" value="trace" /><br />在dev环境url地址中带“跟踪参数=1”可让非html头输出debug信息
        </fieldset>
        <fieldset>
        	<legend>数据库连接设置</legend>
            不配数据库：
            <input name="nodb" onClick="javascript:setdb(this)" type="checkbox" id="nodb" value="1"><br />
            <div id="dbsetlist">
数据库引擎：
<select name="type" id="type">
    <option value="mysql">mysql</option>
                <option value="mysqli">mysqli</option>
                <option value="pdomysql">pdomysql</option>
            </select><br />
            数据库地址：<input type="text" name="host" id="host" class="txt" value="localhost" /><br />
            数据库端口：<input type="text" name="port" id="port" class="txt" value="3306" /><br />
            数据库名称：<input type="text" name="dbname" id="dbname" class="txt" /><br />
            数据表前缀：<input type="text" name="perfix" id="perfix" value="tbs_" class="txt" /><br />
            用&nbsp;&nbsp;户&nbsp;&nbsp;名：<input type="text" name="user" id="user" class="txt" /><br />
            密&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;码：<input type="text" name="pass" id="pass" class="txt" /><br />
            </div>
        </fieldset>
        <br /><input type="submit" class="button" name="s1" id="s1" value="开始安装" />
        </form></dd>
        <?php }?>
    </dl>
    <?php }?>
    <div class="copyright">Copyright &copy; <a href="http://www.tynt.cn" target="_blank">TYNT.CN</a> 2013&nbsp;&nbsp;&nbsp;share happiness, simple learning</div>
</body>
</html>