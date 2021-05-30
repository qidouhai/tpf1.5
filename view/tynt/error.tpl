<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $param['charset']?>">
<meta name="rebots" content="noindex,nofollow" />
<meta name="Author" content="TYNT.CN" />
<title><?php echo $param['language']['TYNT_ERROR_TITLE'];?></title>
<style type="text/css">
h2 {color:#f00;border-bottom:2px solid #ccc;padding:6px}
h5 {padding:3px;line-height:20px}
.site,.trace p {background:#FBF2D9;padding:3px;line-height:18px;font-size:12px}
.copyright {font-family:Arial;text-align:center}
</style>
</head>
<body>
	<h2><?php echo $param['app']?><?php echo $param['language']['TYNT_ERROR_TITLEINTRO'];?></h2>
    <h5><b><?php echo $param['language']['TYNT_ERROR_INTRO'];?>:</b>[<?php echo $param['exception'];?>]<?php echo $param['message']?>&nbsp;&nbsp;&nbsp;[<a href="<?php echo(strip_tags($_SERVER['REQUEST_URI']))?>"><?php echo $param['language']['TYNT_ERROR_REPLAY'];?></a>] | [<a href="javascript:history.back()"><?php echo $param['language']['TYNT_ERROR_BACK'];?></a>]</h5>
    <?php if($param['debug']){?>
    <h5 class="site"><b><?php echo $param['language']['TYNT_ERROR_SITE'];?>:</b><span class="red">File:<?php echo $param['file']?></span>&nbsp;&nbsp;&nbsp;Line:<?php echo $param['line']?></h5>
    <div class="trace">
    	<h5><?php echo $param['language']['TYNT_ERROR_TRACE'];?></h5>
        <p><?php echo nl2br($param['trace']);?></p>
    </div>
    <?php }?>
    <div class="copyright">Copyright &copy; TYNT.CN 2013&nbsp;&nbsp;&nbsp;share happiness, simple learning</div>
</body>
</html>