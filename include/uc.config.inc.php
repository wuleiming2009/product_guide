<?php
$uc_config = array(
	//sdk server的接口地址
	//端游服务端测试环境访问地址：http://sdk.test4.g.uc.cn/
	//端游服务端生产环境访问地址：http://sdk.g.uc.cn/
	//'serverHost'=>"http://sdk.test4.g.uc.cn/",
	'serverHost'=>"http://sdk.g.uc.cn/",
        
    //接口的路由地址：接口名_url=路由地址
    'ucid.bind.create_url'=>"ss",
    'ucid.game.gameData_url'=>"ss",
    'system.getIPList_url'=>"ss",
    'account.verifySession_url'=>"cp/account.verifySession",
    
    //CPID
    'cpId' => 48376,
	//游戏编号
	'gameId' => 544062,
	//分配给游戏合作商的接入密钥,请做好安全保密
	'apiKey' => "2d8b83f861957f4f7671a3010af21c5c",
	
	//以下是相关时间参数的配置
	//连接超时时间【单位:秒】 默认:5
	'connectTimeOut' => 5,
	//获取IP列表间隔的时间【单位:小时】默认:24
	'intervalTime' => 24,
	//线程检测域名的时间间隔【单位:秒】默认:10
	'checkTime' => 10
);