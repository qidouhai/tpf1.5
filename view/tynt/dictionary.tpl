<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $param['charset'];?>">
<meta name="rebots" content="noindex,nofollow" />
<meta name="Author" content="TYNT.CN" />
<title><?php echo $param['language']['TYNT_DICTIONARY_TITLE'];?></title>
<style type="text/css">
h1 {text-align:center;}
td,th {font-size:12px;}
.tt5 {background:#eee}
.t5 {padding:0 2px;background:#fff}
.t1,.tt1 {width:90px;background:#fff;padding:0 2px}
.t2,.tt2 {width:160px;background:#fff;padding:0 2px}
.t3,.tt3 {width:40px;background:#fff;padding:0 2px;text-align:center}
.t4,.tt4 {width:60px;background:#fff;padding:0 2px}
.tt1,.tt2,.tt3,.tt4 {background:#eee;text-align:center}
.copyright {font-family:Arial;text-align:center}
.copyright a {color:#009;text-decoration:none}
span {padding-top:15px;display:block;font-size:14px;font-weight:600}
</style>
</head>
<body>
	<h1><?php echo $param['language']['TYNT_DICTIONARY_TITLE'];?></h1>
	<?php foreach($param['db'] as $v){?>
    <span><?php echo $v['name'];?>（<?php echo $v['comment']?>）<?php echo $param['language']['TYNT_LEFT_BRACKET'].$v['engine'];?>/<?php echo $v['format'].$param['language']['TYNT_RIGHT_BRACKET'];?></span>
    <table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#000000">
      <tr> 
        <th class="tt1"><?php echo $param['language']['TYNT_DICTIONARY_FIELDNAME'];?></th>
        <th class="tt2"><?php echo $param['language']['TYNT_DICTIONARY_FIELDTYPE'];?></th>
        <th class="tt3"><?php echo $param['language']['TYNT_DICTIONARY_FIELDISNULL'];?></th>
        <th class="tt4"><?php echo $param['language']['TYNT_DICTIONARY_FIELDDEFAULT'];?></th>
        <th class="tt5"><?php echo $param['language']['TYNT_DICTIONARY_FIELDCOMMENT'];?></th>
      </tr>
      <?php foreach($v['fields'] as $val){?>
      <tr>
      	<td class="t1"><?php echo $val['field'];?></td>
      	<td class="t2"><?php echo $val['type'];

      	?></td>
      	<td class="t3"><?php echo $val['isnull'];?></td>
      	<td class="t4"><?php echo $val['default'];?></td>
      	<td class="t5"><?php echo $val['comment'];?></td>
      </tr>
      <?php }?>
    </table>
    <table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#000000">
      <tr>
        <th class="tt1"><?php echo $param['language']['TYNT_DICTIONARY_INDEXNAME'];?></th>
        <th class="tt5"><?php echo $param['language']['TYNT_DICTIONARY_INDEXCOMMENT'];?></th>
      </tr>
      <?php foreach($v['indexs']['name'] as $key=>$val) {?>
      <tr>
        <td class="t1"><?php echo $key;?></td>
        <td class="t5"><?php echo implode(',',$val);?>&nbsp;(<?php echo $param['language']['TYNT_DICTIONARY_INDEXUNIQUE'];?>:<?php if($v['indexs']['unique'][$key]==0) echo 'YES'; else echo 'NO'?>)</td>
      </tr>
      <?php }?>
    </table>
    <?php }?>  
    <div class="copyright">Copyright &copy; <a href="http://www.tynt.cn" target="_blank">TYNT.CN</a> 2013&nbsp;&nbsp;&nbsp;share happiness, simple learning</div>
</body>
</html>