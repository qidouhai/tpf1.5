<?php
if(Config::getConfig('debugtype')==1){
?>
<script type="text/javascript">
console.log("%c<?php echo $param['app']?><?php echo $param['language']['TYNT_DEBUG_TITLE'];?>\n%c<?php echo $param['language']['TYNT_DEBUG_MEMERY'];?>:<?php echo round($param['memery']/1024,2);?>KB, <?php echo $param['language']['TYNT_DEBUG_TIME'];?>:<?php echo round($param['time'],4);?>s, <?php echo $param['language']['TYNT_DEBUG_QUERY_TIMES'];?>:<?php echo $param['db_query_times']?>, <?php echo $param['language']['TYNT_DEBUG_MODULE'];?>:<?php echo $param['router']['module']?>, <?php echo $param['language']['TYNT_DEBUG_CONTROLLER'];?>:<?php echo $param['router']['controller']?>, <?php echo $param['language']['TYNT_DEBUG_ACTION'];?>:<?php echo $param['router']['action']?>, <?php echo $param['language']['TYNT_DEBUG_MODEL'];?>:<?php echo $param['router']['model']?>, <?php echo $param['language']['TYNT_DEBUG_VIEW'];?>:<?php echo $param['router']['view'];?>\n","color:#f00;font-size:18px;font-weight:600","color:#f60","\n");
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_LOG'];?>(ALETR:<?php echo $param['logtimes'][0];?> ERROR:<?php echo $param['logtimes'][1];?> WRANING:<?php echo $param['logtimes'][2];?> NOTICE:<?php echo $param['logtimes'][3];?> INFO:<?php echo $param['logtimes'][4];?> DEBUG:<?php echo $param['logtimes'][5];?>)");
console.log("<?php echo str_replace(array("\r", "\n"), array('', '\n'),implode('\n',str_replace('\\','\\\\',$param['log'])));?>");
console.groupEnd();
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_ROUTER'];?>");
console.log("<?php echo $param['language']['TYNT_DEBUG_MODULE'];?>:<?php echo $param['router']['module']?>\n<?php echo $param['language']['TYNT_DEBUG_CONTROLLER'];?>:<?php echo $param['router']['controller']?>\n<?php echo $param['language']['TYNT_DEBUG_ACTION'];?>:<?php echo $param['router']['action']?>\n<?php echo $param['language']['TYNT_DEBUG_MODEL'];?>:<?php echo $param['router']['model']?>\n<?php echo $param['language']['TYNT_DEBUG_VIEW'];?>:<?php echo $param['router']['view'];?>\n<?php echo $param['language']['TYNT_DEBUG_URI'];?>:<?php echo $param['router']['uri']?>");
<?php if($param['router']['param']!='') {?>
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_PARAM'];?>");    
<?php foreach($param['router']['param'] as $k=>$v){?>
    console.log("<?php echo $k;?>=<?php echo $v;?>\n");
<?php }}?>
console.groupEnd();
console.groupEnd();
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_ENV'];?>");
console.log("<?php echo $param['language']['TYNT_DEBUG_SESSION_ID'];?>:<?php echo $param['server']['SESSION_ID']?>\n<?php echo $param['language']['TYNT_DEBUG_SERVER_NAME'];?>:<?php echo $param['server']['SERVER_NAME']?>\n<?php echo $param['language']['TYNT_DEBUG_SERVER_ADDR'];?>:<?php echo $param['server']['SERVER_ADDR']?>\n<?php echo $param['language']['TYNT_DEBUG_SERVER_PORT'];?>:<?php echo $param['server']['SERVER_PORT']?>\n<?php echo $param['language']['TYNT_DEBUG_MOTHOD'];?>:<?php echo $param['method']?>\n<?php echo $param['language']['TYNT_DEBUG_SCRIPT'];?>:<?php echo $param['script']?>\n<?php echo $param['language']['TYNT_DEBUG_USER_AGENT'];?>:<?php echo $param['client']['HTTP_USER_AGENT']?>\n<?php echo $param['language']['TYNT_DEBUG_CLIENT_ADDR'];?>:<?php echo $param['client']['CLIENT_ADDR']?>\n<?php echo $param['language']['TYNT_DEBUG_CLIENT_PORT'];?>:<?php echo $param['client']['CLIENT_PORT']?>");
console.groupEnd();
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_INCLUDE_FILE'];?>(<?php echo count($param['include']);?>)");
console.log("<?php echo str_replace('TYNT','\n',str_replace('\\','\\\\',implode('TYNT',$param['include'])));?>");
console.groupEnd();
var _debug_variable = {};
<?php 
$i=1;
foreach($param['param'] as $k=>$v){?>
    _debug_variable.NO<?php echo $i;?> = {"Key":"<?php echo $k;?>","Value":"<?php echo $v;?>"}
<?php 
$i++;
}?>
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_VARIABLE'];?>(<?php echo $i-1;?>)");
console.table(_debug_variable);    
console.groupEnd();
console.groupCollapsed("<?php echo $param['language']['TYNT_DEBUG_UNIT'];?>");
var _debug_unit = {};
<?php 
$i=1;
if($param['unitshow']){
foreach($param['unit']['name'] as $v){?>
    _debug_unit.NO<?php echo $i;?> = {"Tags":"<?php echo $v?>","<?php echo $param['language']['TYNT_DEBUG_UNIT_MEMERY'];?>":"<?php echo round($param['unit']['memery'][$v]/1024,2).KB;?>","<?php echo $param['language']['TYNT_DEBUG_UNIT_TIME'];?>":"<?php echo round($param['unit']['time'][$v],6).s;?>","<?php echo $param['language']['TYNT_DEBUG_UNIT_QUERY_TIMES'];?>":"<?php echo $param['unit']['db_query_times'][$v]?>"}
<?php 
$i++;
}}?>
console.table(_debug_unit);    
console.groupEnd();
</script>
<?php }else{?>
<style type="text/css">
.TPF_debugFrame {}
.TFP_debugPad {height: 45px}
.TPF_debug {z-index:9999;position:fixed;bottom:0;font-size:12px;width:100%;height:30px;background:#f9f9f9;left:0;overflow:hidden;}
.TPF_debugLine {background:#efefef;color:#214;cursor:pointer;width:100%;border-top:1px solid #aaa;height: 30px;line-height:30px;list-style: none;padding:0;margin:0;border-bottom: 1px solid #aaa;filter:alpha(opacity=80);-moz-opacity:0.8;opacity:0.8}
.TPF_debugLine li {border-left:1px solid #f1f1f1;border-right:1px solid #bbb;float:left;padding:0 8px;}
.TPF_debugLine li s {text-decoration: none;color:#f60;}
.TPF_debugBox {overflow-y:scroll;height:350px;width:60%;padding:10px}
.TPF_debug h2 {margin-top:8px;color:#f00;border-bottom:2px solid #ccc;padding:6px}
.TPF_debug dt {font-weight:bold;}
.TPF_debug dd {line-height:20px;word-wrap:break-word}
.TPF_debug dd hr {width:360px;float:left}
</style>
<script type="text/javascript">
function TPF_DebugshowAndHide(obj) {
    if(obj.getAttribute("data")=='hide') {
        obj.parentNode.style.height="400px";
        obj.parentNode.parentNode.children[0].style.height="450px";
        obj.setAttribute("data","show");
    }else{
        obj.parentNode.style.height="30px";
        obj.parentNode.parentNode.children[0].style.height="45px";
        obj.setAttribute("data","hide");
    }
	
}
function TFP_Close_Debugbox(obj) {
    obj.parentNode.parentNode.parentNode.style.display="none";
}
</script>
<div class="TPF_debugFrame">
<div class="TFP_debugPad"></div>
<div class="TPF_debug">
    <ul class="TPF_debugLine" data="hide" onclick="javascript:TPF_DebugshowAndHide(this)">
        <li onclick="TFP_Close_Debugbox(this)">X</li>
        <li><?php echo $param['language']['TYNT_DEBUG_MEMERY'];?>:<s><?php echo round($param['memery']/1024,2);?>KB</s></li>
        <li><?php echo $param['language']['TYNT_DEBUG_TIME'];?>:<s><?php echo round($param['time'],4);?>s</s></li>
        <li><?php echo $param['language']['TYNT_DEBUG_MODULE'];?>:<s><?php echo $param['router']['module']?></s></li>
        <li><?php echo $param['language']['TYNT_DEBUG_CONTROLLER'];?>:<s><?php echo $param['router']['controller']?></s></li>
        <li><?php echo $param['language']['TYNT_DEBUG_ACTION'];?>:<s><?php echo $param['router']['action']?></s></li>
        <li><?php echo $param['language']['TYNT_DEBUG_INCLUDE_FILE'];?>:<s><?php echo count($param['include']);?></s></li>
        <li>ALETR:<s><?php echo $param['logtimes'][0];?></s>&nbsp;ERROR:<s><?php echo $param['logtimes'][1];?></s>&nbsp;WRANING:<s><?php echo $param['logtimes'][2];?></s>&nbsp;NOTICE:<s><?php echo $param['logtimes'][3];?></s>&nbsp;INFO:<s><?php echo $param['logtimes'][4];?></s>&nbsp;DEBUG:<s><?php echo $param['logtimes'][5];?></s></li>
    </ul>
    <div class="TPF_debugBox">
	<h2><?php echo $param['app']?><?php echo $param['language']['TYNT_DEBUG_TITLE'];?></h2>
    <dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_LOG'];?></dt>
        <?php foreach($param['log'] as $v) {?>
        <dd><?php echo $v?></dd>
        <?php }?>
    </dl>
    <dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_ROUTER'];?></dt>
        <dd><?php echo $param['language']['TYNT_DEBUG_MODULE'];?>:<?php echo $param['router']['module']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_CONTROLLER'];?>:<?php echo $param['router']['controller']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_ACTION'];?>:<?php echo $param['router']['action']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_MODEL'];?>:<?php echo $param['router']['model']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_VIEW'];?>:<?php echo $param['router']['view'];?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_PARAM'];?>:<?php print_r($param['router']['param'])?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_URI'];?>:<?php echo $param['router']['uri']?></dd>
    </dl>
    <dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_ENV'];?></dt>
        <dd><?php echo $param['language']['TYNT_DEBUG_SESSION_ID'];?>:<?php echo $param['server']['SESSION_ID']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_SERVER_NAME'];?>:<?php echo $param['server']['SERVER_NAME']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_SERVER_ADDR'];?>:<?php echo $param['server']['SERVER_ADDR']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_SERVER_PORT'];?>:<?php echo $param['server']['SERVER_PORT']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_MOTHOD'];?>:<?php echo $param['method']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_SCRIPT'];?>:<?php echo $param['script']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_USER_AGENT'];?>:<?php echo $param['client']['HTTP_USER_AGENT']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_CLIENT_ADDR'];?>:<?php echo $param['client']['CLIENT_ADDR']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_CLIENT_PORT'];?>:<?php echo $param['client']['CLIENT_PORT']?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_QUERY_TIMES'];?>:<?php echo $param['db_query_times']?></dd>
    </dl>
	<dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_INCLUDE_FILE'];?></dt>
        <?php foreach($param['include'] as $v){?>
        <dd><?php echo $v?></dd>
        <?php }?>
    </dl>
    <dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_VARIABLE'];?>(<?php echo count($param['param']);?>)</dt>
        <?php foreach($param['param'] as $k=>$v){?>
        <dd><?php echo $k,':',$v?></dd>
        <?php }?>
    </dl>
    <dl>
    	<dt><?php echo $param['language']['TYNT_DEBUG_UNIT'];?></dt>
        <?php 
        if($param['unitshow']){
        foreach($param['unit']['name'] as $v){?>
        <dd>Tags:<?php echo $v?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_UNIT_MEMERY'];?>:<?php echo $param['unit']['memery'][$v]?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_UNIT_TIME'];?>:<?php echo $param['unit']['time'][$v]?></dd>
        <dd><?php echo $param['language']['TYNT_DEBUG_UNIT_QUERY_TIMES'];?>:<?php echo $param['unit']['db_query_times'][$v]?></dd>
        <dd><hr /></dd>
        <?php }}?>
    </dl>
    </div>
</div>
</div>
<?php }?>