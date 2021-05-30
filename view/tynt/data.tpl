<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $param['charset'];?>">
<meta name="rebots" content="noindex,nofollow" />
<meta name="Author" content="TYNT.CN" />
<title><?php echo $param['language']['TYNT_TABLEDATA_TITLE'];?></title>
<style type="text/css">
h1 {text-align:center;}
td,th {font-size:12px;background:#eee;padding:2px 1px;}
td {background:#fff}
.copyright {font-family:Arial;text-align:center}
.copyright a {color:#009;text-decoration:none}
span {padding-top:15px;display:block;font-size:14px;font-weight:600}
</style>
</head>
<body>
	<h1><?php echo $param['language']['TYNT_TABLEDATA_TITLE'];?></h1>
	<?php foreach($param['data'] as $v){?>
    <span><?php echo $v['name'];?>（<?php echo $v['comment']?>）<?php echo $param['language']['TYNT_LEFT_BRACKET'].$v['engine'];?>/<?php echo $v['format'].$param['language']['TYNT_RIGHT_BRACKET'];?></span>
    <table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#000000">
      <tr>
        <?php foreach($v['fields'] as $fv){?>
        <th><?php echo $fv['field'];?>(<?php echo $fv['comment'];?>)</th>
        <?php }?>
      </tr>
      <?php foreach($v['data'] as $dv) {?>
      <tr>
        <?php foreach($dv as $key=>$val){?>
        <td><?php echo $val?></td>
        <?php }?>
      </tr>
      <?php }?>
    </table>
    <?php }?>  
    <div class="copyright">Copyright &copy; <a href="http://www.tynt.cn" target="_blank">TYNT.CN</a> 2013&nbsp;&nbsp;&nbsp;share happiness, simple learning</div>
</body>
</html>