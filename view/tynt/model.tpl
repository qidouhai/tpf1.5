<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $param['charset'];?>">
<meta name="rebots" content="noindex,nofollow" />
<meta name="Author" content="TYNT.CN" />
<title><?php echo $param['title'];?></title>
<style type="text/css">
h1 {text-align:center;}
.copyright {font-family:Arial;text-align:center}
.copyright a {color:#009;text-decoration:none}
span {padding-top:15px;display:block;font-size:14px;font-weight:600}
</style>
</head>
<body>
	  <h1><?php echo $param['title'];?></h1>
    <ul>
	  <?php foreach($param['result'] as $v){?>
      <li><?php echo $v;?></li>
    <?php }?>
    </ul> 
    <div class="copyright">Copyright &copy; <a href="http://www.tynt.cn" target="_blank">TYNT.CN</a> 2013&nbsp;&nbsp;&nbsp;share happiness, simple learning</div>
</body>
</html>