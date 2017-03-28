<?php
include_once("../include/globle.php");

//$nickname = $_GET['user'];
//$gamesite = $_GET['site'];
//if (!empty($nickname)){
//    $sql = "select id from gamecenter.users where nickname = '$nickname'";
//    if ($gamesite) $sql = $sql." and site='$gamesite'";
//    $rows = mysql_query($sql);
//    list($id,$name) = mysql_fetch_row($rows);
//    $_SESSION["userId"] = $id;
//}

$_SESSION["loginttime"] = time();
$_SESSION['SID'] = md5('mc');

if ($_POST["action"]){

    $seed = Daemon_Efuns::log_event("request_debug_rest",$_REQUEST);
    $_SESSION['seed'] = $seed;

    $module = $_REQUEST['daemon'];
    $action = $_REQUEST['action'];
    $arguments = array();

    while ( list( $key, $val ) = each( $_REQUEST ) ){
        if ($key == 'daemon') continue;
        if ($key == 'action') continue;
        if ($key == 'sign') continue;
        
        $arguments["$key"] = $val;
    }
    //$module = login
    $mod_file = DAEMON_DIR.$module.'app.php';
    if ( file_exists($mod_file) ) {
        include_once($mod_file);
    }else {
        echo "9999";
    }
    
    //Daemon_loginApp
    $staticClassName = 'Daemon_'.$module.'App';
    call_user_func( $staticClassName.'::'.$action, $arguments);
    exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<SCRIPT src="jquery-1.4.2.min.js" type="text/javascript"></SCRIPT>
<SCRIPT src="dump.js" type="text/javascript"></SCRIPT>
<SCRIPT src="jquery.slg.js" type="text/javascript"></SCRIPT>
<title>测试360</title>
<style type="text/css">
<!--
body,td,th {
	font-family: 微软雅黑;
	font-size: 14px;
}

#Alt{
text-align:left;
width:auto;
z-index:1001;
position:absolute;
display:none;
height: auto;
background-color:#0C1B1A;
border:1px solid #6E846A;
filter:alpha(opacity=60);
}
#Alt1{
width:auto;
height:auto;
border:0px solid #6E846A;
}
#Alt2{
width:auto;
height:auto;
border:0px solid #353C28;
color:yellow;
}
#altCon{
color:#C6E7FF;
padding:5px;
}
-->
</style></head>
<script>

function AltStr(event,str)
{
	event=event ? event : window.event;
	obj=event.srcElement? event.srcElement : event.target;
	AShowA(event,str);
	$(obj).bind("mouseout",function(){$("#Alt").hide(0);$(obj).unbind();});
}
function AShowA(event,str)
{
	event=event ? event : window.event;
	$("#Alt").css("left",event.clientX+10);
	$("#Alt").css("top",event.clientY+10);
	$("#altCon").css("width", (str.length * 20) + "px");
	$("#altCon").css("height","auto");
	$("#Alt").show(0);
	var tt = setTimeout(function(){clearTimeout(tt);$("#Alt").hide(0);},5000);
	$("#altCon").html(str);
}

function doTest( tModule , tAction)
{
    xunQin_call_server('testCase', tAction , "daemon=" + tModule + "&" + ($('#'+tAction).val()) );
}

$(function() {
	$.ajax({type: "post", url: "test.php", data:"", success: function(msg) {
		var jsonmsg = eval("("+msg+")");
		var html = "";
		$.each(jsonmsg, function(key, val) {
			if(val[1] == undefined) {
				html += val[0]+"<br/>";
			} else {				
				html += "<a href=\"javascript:doTest('"+val[1]+"','"+val[2]+"')\">"+val[0]+" - "+val[1]+"App:"+val[2]+"</a>";
  				html += "<label for='args'>参数：</label>";
  				html += "<input id=\""+val[2]+"\" name='args' type='text' id='args' size='60'/>";
				html += "[<a href=\"javascript:addExample('"+val[2]+"', '"+val[3]+"')\" onMouseOver=\"AltStr(event,'点这里加载参数范例');\">参数范例</a>]<br />";
			}
		});
		$("#form1").html(html);
	}});
});

function addExample(tAction, array)
{
	var val = "";
	var strArr = array.split("|");
	for(var i = 0; i < strArr.length; i ++) {
		if(strArr[i].indexOf(":")<0)
		{
			val += strArr[i];
		}
		else
		{
			var strArr2 = strArr[i].split(":");
			val += strArr2[0] + "=" + strArr2[1];
	
		}	
		if(i != strArr.length - 1) val += "&";
	}

    $('#'+tAction).val(val);
}

</script>
<!--ALT-->
<div id="Alt" onmouseout='$("#Alt").hide(0);'>
	<div id="Alt1">
		<div id="Alt2">
			<div id="altCon" style="color:#C6E7FF;padding:5px;"></div>
		</div>
	</div>
</div>

<body>

	<form id="form1" name="form1" method="post" action="">

	</form>
	
	<textarea id="ret" rows="30" cols="100">
这里将会返回调试信息
    </textarea>

</body>
</html>
