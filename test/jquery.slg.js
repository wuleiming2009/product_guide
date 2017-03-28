xunQin_gameSetting = {
    heartbeat: 6000,           //心跳
    timertick: 1000,           //心跳
    isKeeplive: true ,
    callback : null
};

//最多允许执行任务数

var TC_NUM = 5;
var HB_NUM = 5;

var xunQin_tcBuffers = new Array();
var xunQin_hbBuffers = new Array();


//flash通道调用 uid为0时发给所有在线用户
function flashChannel(uid,func,args)
{
	thisMovie("chatdiv").sendToServer(uid, func, args);
}
function receivedFromServer(func, args)
{
    try{
        eval(  func+"(args)" );
    }
    catch(err){
    }
    return;
}
function initServer()
{
}

function connectServer()
{
    document.title = "寻秦 - 寻找我们永恒的帝国";
}

function disconnectServer()
{
}


//响应来自服务器的push数据
xunQin_ServerPush = function(buffers)
{
    try{
        datainfo = eval( '(' + decode64(buffers) + ')' ) ;
    }
    catch(err){
        alert('500请求失败');
        return;
    }
	//alert(buffers);
    if (datainfo["func"]){
        func_arg = datainfo["args"];
        eval( datainfo["func"] + "(func_arg)" );
    }
	//alert(datainfo["data"]);
    if (datainfo["data"]){
        func_arg = datainfo["args"];
        eval( datainfo["data"] + " = func_arg" );
		//alert(datainfo["data"] + " = "+func_arg);

    }
    return;
}

//ajax返回事件
xunQin_ajaxCallback = function(buffers)
{
    $("#ret").val(buffers);
	return;
	
    if (!datainfo.hasOwnProperty("responseData")) return;
    if (datainfo["responseData"] == undefined) return;
    if (datainfo["responseData"]){
        for(var i=0; i < datainfo["responseData"].length; i++){
            dataone = datainfo["responseData"][i];
            if (dataone["func"]){
                func_arg = dataone["args"];
                eval( dataone["func"] + "(func_arg)" );
            }
            if (dataone["data"]){
                func_arg = dataone["args"];
				//alert(dataone["data"] + " = "+func_arg+' |js');
                eval( dataone["data"] + " = func_arg" );
            }

            if (dataone["callback"]){
                callback_filename = dataone["callback"]["filename"];        //必须是字符串
                callback_function = dataone["callback"]["function"];        //必须是字符串
                callback_timeout = dataone["callback"]["timeout"];
                callback_args = dataone["callback"]["args"];        //这个是变量 不能加引号
                CT1 = setTimeout( "xunQin_call_server('" +callback_filename+"' ,'" +callback_function+ "'," +callback_args+ ")", callback_timeout);
            }
        }
    }

    if (datainfo["disconnect"]){
        Xunqin_gameSettings.isKeeplive = false;
    }
};

//ajax发送失败 retry
function send_failed(from_data)
{
	alert('403发送失败 ');
}

//ajax呼叫php module&function
function xunQin_call_server( filen, func , args )
{
    strData = "action=" + func + "&" + args;
    //alert(strData);
    //strData = { "action": func, "args" : args };
	$.ajax({
	    type: "POST",
        url: filen + ".php",
        data: strData,
        cache: false,
        success: xunQin_ajaxCallback,
        error :send_failed
    });
};


//回调函数
function xunQin_ServerPull( bufferId )
{
    xunQin_call_server( 'getBuffer', 'pull' , "bufferid="+bufferId );
}


//心跳6s
function xunQin_heartbeat()
{
    for(var i=0; i < xunQin_hbBuffers.length; i++)
    {
        func_name = xunQin_hbBuffers[i];
        eval( func_name + "()" );
    }
    HB = setTimeout( xunQin_heartbeat , xunQin_gameSetting.heartbeat);
};


//计时器1s
function xunQin_ticktime()
{
    for(var i=0; i < xunQin_tcBuffers.length; i++)
    {
        func_name = xunQin_tcBuffers[i];
        eval( func_name + "()" );
    }
    //TC = setTimeout( ticktime , xunQin_gameSetting.timertick);
};


//注册函数到心跳
function xunQin_hearbeat_register( func_name )
{
    if (xunQin_hbBuffers.length >= HB_NUM ) return 0;
    xunQin_hbBuffers.push( func_name );
    return 1;
};


//注册函数到计时器
function xunQin_ticktime_register( func_name )
{
    if (xunQin_tcBuffers.length >= TC_NUM ) return 0;
    xunQin_tcBuffers.push( func_name );
    return 1;
};



//计时开始
$(document).ready(function(){

    xunQin_heartbeat();
    setInterval(xunQin_ticktime,xunQin_gameSetting.timertick);

});





var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function encode64(input) {
   input = escape(input);
   var output = "";
   var chr1, chr2, chr3 = "";
   var enc1, enc2, enc3, enc4 = "";
   var i = 0;

   do {
      chr1 = input.charCodeAt(i++);
      chr2 = input.charCodeAt(i++);
      chr3 = input.charCodeAt(i++);

      enc1 = chr1 >> 2;
      enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
      enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
      enc4 = chr3 & 63;

      if (isNaN(chr2)) {
         enc3 = enc4 = 64;
      } else if (isNaN(chr3)) {
         enc4 = 64;
      }

      output = output +
         keyStr.charAt(enc1) +
         keyStr.charAt(enc2) +
         keyStr.charAt(enc3) +
         keyStr.charAt(enc4);
      chr1 = chr2 = chr3 = "";
      enc1 = enc2 = enc3 = enc4 = "";
   } while (i < input.length);

   return output;
}

function decode64(input) {
   var output = "";
   var chr1, chr2, chr3 = "";
   var enc1, enc2, enc3, enc4 = "";
   var i = 0;

   // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
   var base64test = /[^A-Za-z0-9\+\/\=]/g;
   if (base64test.exec(input)) {
      alert("There were invalid base64 characters in the input text.\n" +
            "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
            "Expect errors in decoding.");
   }
   input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

   do {
      enc1 = keyStr.indexOf(input.charAt(i++));
      enc2 = keyStr.indexOf(input.charAt(i++));
      enc3 = keyStr.indexOf(input.charAt(i++));
      enc4 = keyStr.indexOf(input.charAt(i++));

      chr1 = (enc1 << 2) | (enc2 >> 4);
      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
      chr3 = ((enc3 & 3) << 6) | enc4;

      output = output + String.fromCharCode(chr1);

      if (enc3 != 64) {
         output = output + String.fromCharCode(chr2);
      }
      if (enc4 != 64) {
         output = output + String.fromCharCode(chr3);
      }

      chr1 = chr2 = chr3 = "";
      enc1 = enc2 = enc3 = enc4 = "";

   } while (i < input.length);

   return unescape(output);
}