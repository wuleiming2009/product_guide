<?php
//
//  goods    10000+
//  fitments 20000+
//  equip 30000+
//  material 40000+
//  quest  50000+
//  plus  (商城付费道具)  60000+ 
//  plus  (游戏普通道具)  65000+ 


$dbh = mysql_connect("localhost","mysql","xunqin1.1");
mysql_query("use mc");
mysql_query("set names utf8");



////导入货物
//$type = "goods";
//$filename = "goodstype.txt";
//$lines = file($filename);
//$sqlleft1 = "insert into itemtype (id,name,img,`describe`,type,level,buyprice,saleprice,maxnum,bandingable,goldprice,silverprice) values ";
//$sqlleft2 = "insert into goodstype (id,tradeType,goodsPrice,gameset) values ";
//
//
//mysql_query("delete from itemtype where id>10000 and id < 20000");
//mysql_query("delete from goodstype where id>10000 and id < 20000");
//
//$id = 10000;
//foreach($lines as $line){
//    $id ++;
//    $tabs = explode("\t",trim($line));
//    $itemtypeSql = $sqlleft1."($id,'".addslashes(trim($tabs[3]))."','".trim($tabs[5])."','".addslashes(trim($tabs[4]))."','$type','".str_replace('级','',trim($tabs[7]))."','".trim($tabs[8])."','1','1000','0',0,0)";
//    if ( $tabs[0] == '酒楼' ){
//        $subtype = 2;
//    }else if ( $tabs[0] == '客栈' ){
//        $subtype = 3;
//    }else {
//        $subtype = 1;
//    }
//    
//    $gameset = 1;
//    if ($tabs[2] == '普通货物'){
//        $gameset = 0;
//    }
//    
//    $goodstypeSql = $sqlleft2."($id,'$subtype','".trim($tabs[8])."','$gameset')";
//    
//    echo $itemtypeSql."\n";
//    $res = mysql_query($itemtypeSql);
//    echo mysql_affected_rows();
//    
//    echo $goodstypeSql."\n";
//    $res = mysql_query($goodstypeSql);
//    echo mysql_affected_rows();
//}
//
//echo "goods done\n";


////导入材料
//$type = "material";
//$filename = "materialtype.txt";
//$id = 40000;
//
//$lines = file($filename);
//$sqlleft1 = "insert into itemtype (id,name,img,`describe`,type,level,buyprice,saleprice,maxnum,bandingable,goldprice,silverprice) values ";
//$sqlleft2 = "insert into materialtype (id,smallimg,createfrom) values ";
//
//mysql_query("delete from itemtype where id>$id and id < ".($id+10000));
//mysql_query("delete from materialtype where id>$id and id < ".($id+10000));
//
//foreach($lines as $line){
//    $id ++;
//    $tabs = explode("\t",trim($line));
//    $itemtypeSql = $sqlleft1."($id,'".addslashes(trim($tabs[1]))."','".trim($tabs[4])."','".addslashes(trim($tabs[1]))."','$type','1','".trim($tabs[2])."','1','1000','0',0,0)";
//    $materialtypeSql = $sqlleft2."($id,'".trim($tabs[5])."','".trim($tabs[3])."')";
//    
//    echo $itemtypeSql."\n";
//    $res = mysql_query($itemtypeSql);
//    echo mysql_affected_rows();
//    
//    echo $materialtypeSql."\n";
//    $res = mysql_query($materialtypeSql);
//    echo mysql_affected_rows();
//}
//
//echo "done\n";








////导入全部配方（ 配方 - 书籍 一一对应）
//$filename = "workshopgoods.txt";
//$id = 65000;
//
//$lines = file($filename);
//$sqlleft1 = "insert into itemtype (id,name,img,`describe`,type,level,buyprice,saleprice,maxnum,bandingable,goldprice,silverprice) values ";
//$sqlleft2 = "insert into workshopgoodstype (id,name,need,allTime,des,image,isDeal,goodsA,goodsB,goodsC,goodsAnum,goodsBnum,goodsCnum,getGoods,type,price,special1,special2) values ";
//
//mysql_query("delete from itemtype where id>$id and id < ".($id+5000));
//mysql_query("delete from workshopgoodstype");
//
//function trans_level($name)
//{
//    $levels = array("平民",
//                    "从九品",
//                    "正九品",
//                    "从八品",
//                    "正八品",
//                    "从七品",
//                    "正七品",
//                    "从六品",
//                    "正六品",
//                    "从五品",
//                    "正五品",
//                    "从四品",
//                    "正四品",
//                    "从三品",
//                    "正三品",
//                    "从二品",
//                    "正二品",
//                    "从一品",
//                    "正一品");
//    for($i=0; $i < sizeof($levels); $i++){
//        if ($levels[$i] == $name) return $i;
//    }
//    return 0;
//}
//
//foreach($lines as $line){
//    $id ++;
//    $tabs = explode("\t",trim($line));
//    
//    //书籍id
//    //书籍名字
//    $name = addslashes(trim($tabs[2]));
//    if ($name =="" || $name == "-")  $name = addslashes(trim($tabs[1]))."制作书";
//    //书籍图标
//    $img = "image_book_001.jpg";
//    //书籍描述
//    $desc = addslashes(trim($tabs[3]));
//    //配方的购买价格
//    $price = "500";
//    $itemtypeSql = $sqlleft1."($id,'$name','$img','$desc','plus','1','$price','1','10','0',0,0)";
//    echo $itemtypeSql."\n";
//    $res = mysql_query($itemtypeSql);
//    echo mysql_affected_rows();
//
//    //所需物品的类型和数量
//    $goodsA = trim($tabs[6]);
//    if ($goodsA){
//        $sql = "select id from itemtype where name='$goodsA'";
//        $rows = mysql_query($sql);
//        list($newid) = mysql_fetch_row($rows);
//        $goodsA = $newid;
//    }else {
//        $goodsA = 0;
//    }
//    
//    $goodsB = trim($tabs[8]);
//    if ($goodsB){
//        $sql = "select id from itemtype where name='$goodsB'";
//        $rows = mysql_query($sql);
//        list($newid) = mysql_fetch_row($rows);
//        $goodsB = $newid;
//    }else {
//        $goodsB = 0;
//    }
//    //$goodsC = trim($tabs[6]);
//    
//    $goodsAnum = (int) trim($tabs[7]);
//    $goodsBnum = (int) trim($tabs[9]);
//    //$goodsCnum = trim($tabs[6]);
//    
//    //生产的货物ID
//    $getGoods = trim($tabs[1]);
//    $sql = "select id from itemtype where name='$getGoods'";
//    $rows = mysql_query($sql);
//    list($newid) = mysql_fetch_row($rows);
//    $getGoods = $newid;
//    
//    //配方类型
//    if ( $tabs[0] == '酒楼' ){
//        $type = 2;
//    }else if ( $tabs[0] == '客栈' ){
//        $type = 3;
//    }else {
//        $type = 1;
//    }
//    
//    //使用此配方所需的特殊条件1
//    $special1 = "";
//    //使用此配方所需的特殊条件2
//    $special2 = "";
//    
//    
//    //配方ID、名字、品阶要求、生产物品的时间、配方描述、配方图片（库房中的图标）、是否绑定（挪到物品列）
//    $itemtypeSql2 = $sqlleft2."($id,'".addslashes(trim($tabs[1]))."','".trans_level(trim($tabs[4]))."','".(trim($tabs[10])*60)."','".addslashes(trim($tabs[3]))."','$img','0','$goodsA','$goodsB','$goodsC','$goodsAnum','$goodsBnum','$goodsCnum','$getGoods','$type','$price','$special1','$special2')";
//    
//    echo $itemtypeSql2."\n";
//    $res = mysql_query($itemtypeSql2);
//    echo mysql_affected_rows();
//}
//
//echo "done\n";







// 导入装备
// $type = "equip";
// $filename = "equiptype.txt";
// $filename2 = "equipprops.txt";
// $id = 30000;
// mysql_query("delete from itemtype where id>$id and id < ".$id+10000);
// mysql_query("delete from equiptype where id>$id and id < ".$id+10000);


// $positions = array("手持"=>1,"戒指"=>2,"帽子"=>3,"衣服"=>4,"鞋子"=>5, "项链"=>6);
// $equiptype = array("棍"=>1, "剑"=>2, "笔"=>3, "扇"=>4, "戒指"=>5, "帽子"=>6, "头巾"=>7, "衣服"=>8, "鞋"=>9,"项链"=>10);

// $lines2 = file($filename2);
// 先把属性放在数组里面
// foreach($lines2 as $line){
    // $tabs = explode("\t",$line);
    // if (trim($tabs[0])){
        // $lvl = trim($tabs[0]);
    // }
    
    // $postionName = trim($tabs[1]);    
    // 装备等级	部位	初始价格	强化价格	回收价格  基本5(百战	不屈	名声	魅力	搬运	疾走) 前缀5 后缀5 体力
    // $props[$lvl][$postionName] = array("position"     => $positions[$postionName],
                                         // "buyprice"     => (int)trim($tabs[2]),
                                         // "level"        => (int)trim($tabs[0]),
                                         // "equiptype"    => '',
                                         // "itemlevel"    => (int)trim($tabs[0]),
                                         // "suit"         => 0,
                                         // "isshop"       => 1,
                                         // "attack"       => (int)trim($tabs[5]),
                                         // "defense"       => (int)trim($tabs[6]),
                                         // "reputation"       => (int)trim($tabs[7]),
                                         // "charm"       => (int)trim($tabs[8]),
                                         // "carry"       => (int)trim($tabs[9]),
                                         // "scamper"       => (int)trim($tabs[10]),
                                         
                                         // "f_attack"       => (int)trim($tabs[11]),
                                         // "f_defense"       => (int)trim($tabs[12]),
                                         // "f_reputation"       => (int)trim($tabs[13]),
                                         // "f_charm"       => (int)trim($tabs[14]),
                                         // "f_carry"       => (int)trim($tabs[15]),
                                         // "f_scamper"       => (int)trim($tabs[16]),
                                         
                                         // "n_attack"       => (int)trim($tabs[17]),
                                         // "n_defense"       => (int)trim($tabs[18]),
                                         // "n_reputation"       => (int)trim($tabs[19]),
                                         // "n_charm"       => (int)trim($tabs[20]),
                                         // "n_carry"       => (int)trim($tabs[21]),
                                         // "n_scamper"       => (int)trim($tabs[22]),
                                         
                                         // "strength"       => (int)trim($tabs[23]),
                                         
                                         
                                // );
// }

// $lines = file($filename);


// -- [1.棍, 2.剑, 3.笔, 4.扇, 5.戒指, 6.帽子, 7.头巾, 8.衣服, 9.鞋 10.项链] 用下面的
// $sqlleft1 = "insert into itemtype (id,name,img,`describe`,type,level,buyprice,saleprice,maxnum,bandingable,goldprice,silverprice) values ";
// $sqlleft2 = "insert into equiptype ";


// 1	手持	棍	1	竹竿	独钓寒江雪	item_eq001
// foreach($lines as $line){
    // $id ++;
    // $tabs = explode("\t",trim($line));
    
    // $postionName = trim($tabs[1]);
        
    // 名称
    // $name = addslashes(trim($tabs[4]));
    // 图标
    // $img = addslashes(trim($tabs[6]));
    // 描述
    // $desc = addslashes(trim($tabs[5]));
    // 类型type
    // 等级
    // $level = (int)trim($tabs[3]);
    
    // 装备属性定位
    // $propOne = $props[$level][$postionName];
    
    // 购买价格
    // $buyprice = $propOne["buyprice"];
    // $saleprice = 1;
    // $maxnum = 1;
    // $bandingable = 1;
    // $goldprice = 0;
    // $silverprice = 0;
    
    // 装备的类型
    // $propOne["equiptype"] = $equiptype[trim($tabs[2])];
    
    // 放入物品表
    // $itemtypeSql = $sqlleft1."($id,'$name','$img','$desc','$type','$level','$buyprice','1','1','1',0,0)";
    // echo $itemtypeSql."\n";
    // $res = mysql_query($itemtypeSql);
    // echo mysql_affected_rows();
    
    // $sqladdon = "";
    // foreach($propOne as $key=> $value){
        // if ( $key == "buyprice" ) continue;
        // $sqladdon .= ",$key = '$value'";
    // }
    // 进入装备表
    // $goodstypeSql = $sqlleft2." set id=$id ".$sqladdon;
    // echo $goodstypeSql."\n";
    // $res = mysql_query($goodstypeSql);
    // echo mysql_affected_rows();
// }

// echo "goods done\n";


// 导入任务数据
$filenames3 = 'newtask.txt';
$lines3 = file($filenames3);
mysql_query("TRUNCATE table tasktype");
mysql_query("TRUNCATE table taskrewards");
mysql_query("TRUNCATE table taskticket");
mysql_query("TRUNCATE table taskaims");
mysql_query("TRUNCATE table taskdialogoption");
foreach($lines3 as $line)
{
    $tabs = explode("\t", $line);
    // var_dump($tabs);
    // key	编号	名称	目标	等级	类型	开始NPC	结束NPC	描述	涉及			完成方式	奖励			对话																				
									// NPC	道具	战斗		声望	铜钱	物品	对话1	对话2	对话3	对话4	对话5	对话6	对话7	对话8	对话9	对话10	对话11	对话12	对话13	对话14	对话15	对话16	对话17	对话18	对话19	对话20	对话21
    foreach($tabs as &$tabline)
    {
        $tabline = addslashes(trim($tabline));
    }
    $taskid = 0;
    if(empty($tabs[0]))
    {
        continue;
    }
    for($i=10; $i<=79; $i=$i+9)
    {
        // 如果当前这个对话不为空
        if(!empty($tabs[$i]) && trim($tabs[$i]) != '')
        {
            if(empty($tabs[$i+9]) || trim($tabs[$i+9]) == '')
            {
                $grouptype = 0;
                // 插入任务的奖励数据
            }else
            {
                $grouptype = ($i - 1)/9;
            }
            // 插入tasktype表中数据
            $insertsql1 = "INSERT INTO  `tasktype` (`tasktype`, `taskgrade`, `taskkind`, `isaccepttask`, `repeat`, `daily`, `presettime`, `effort`, `taskName` ,`taskdesc`, `taskplus`, `aimsdesc` ,`Aftertaskdesc` ,`BeforeTaskdesc` ,`RewardStr` ,`beginNPC` ,`endNPC` , `missionHelp`, `missionIntro`, `taskgroup`,`grouptype`  )VALUES (1, 1, '".addslashes($tabs[$i])."', '".addslashes($tabs[$i+6])."' ,0,0,0,0,'".addslashes($tabs[2])."', '".addslashes($tabs[9])."', '".addslashes($tabs[$i+2])."', '".addslashes($tabs[3])."', '".addslashes($tabs[$i+7])."', '', '', '".addslashes($tabs[$i+1])."', '".addslashes($tabs[$i+1])."', '','', ".addslashes(($tabs[1] + 0)).", ".addslashes($grouptype).");";
            if(mysql_query($insertsql1))
            {
                $taskid = mysql_insert_id();
                // 玩家的任务对话
                $dialog1 = "insert into `taskdialogoption` (`taskid`, `value`, `option`, `isnext`) values (".$taskid.", '".addslashes($tabs[($i+8)])."', 'A', 0)";
                mysql_query($dialog1) or die(mysql_error().'=='.$dialog1."\n");
                // 任务前提
                if($tabs[1] != 1 && $i == 10)
                {
                    $ticket1 = "insert into `taskticket` (`taskid`, `tickettype`, `ticketname`, `value`) values(".$taskid.", 5, ".($taskid - 1).", 1)";
                    mysql_query($ticket1) or die(mysql_error().'=='.$ticket1."\n");
                    if(!empty($tabs[4]))
                    {
                        $ticket1 = "insert into `taskticket` (`taskid`, `tickettype`, `ticketname`, `value`) values(".$taskid.", 5, ".($taskid - 1).", 1)";
                        mysql_query($ticket1) or die(mysql_error().'=='.$ticket1."\n");
                    }
                    if(!empty($tabs[5]))
                    {
                        $ticket1 = "insert into `taskticket` (`taskid`, `tickettype`, `ticketname`, `value`) values(".$taskid.", 8, 3, ".addslashes($tabs[5]).")";
                        mysql_query($ticket1) or die(mysql_error().'=='.$ticket1.'\n');
                    }
                    
                }
                // 完成条件
                if(!empty($tabs[$i+3]))
                {
                    $aimsql = "insert into `taskaims` (`taskid`, `aimstype`, `aimsname`, `value`) values(".$taskid.", '".$tabs[$i+3]."', '".addslashes($tabs[$i+4])."', '".addslashes($tabs[$i+5])."') ";
                    if(!mysql_query($aimsql))
                    {
                        echo $aimsql."/n";
                        echo mysql_error();
                        exit();
                    }
                }
                // 上一个任务和下一个任务
                if($i == 10)
                {
                    $updatesql = "update tasktype set `nexttaskid` = ".($taskid + 1)." where id = ".$taskid."";
                }else if(empty($tabs[$i + 9]) || trim($tabs[$i+ 9]) == '')
                {
                    $updatesql = "update tasktype set `lasttaskid` = ".($taskid - 1)." where id = ".$taskid."";
                }else
                {
                    $updatesql = "update tasktype set `lasttaskid` = ".($taskid - 1).",`nexttaskid` = ".($taskid + 1)." where id = ".$taskid."";
                }
                mysql_query($updatesql);
                
            }else
            {
                echo mysql_error()."\n";
                echo $insertsql1."--".$i."\t\n";
            }
        }else
        {
            continue ;
        }
    }
    
}


// 导入屏蔽字

// $filenames4 = "badword.txt";
// $filenames3 = 'newtask.txt';
// $lines4 = file($filenames4);
// mysql_query("truncate table badword");
// foreach($lines4 as $line)
// {
    // $lines = explode("\t", $line);
    // print_r($lines);
     // $sql = "insert into badword values('".trim($line)."')";
    // if(mysql_query($sql))
    // {
        
    // }else
    // {
        // echo mysql_error()."--".$sql."\n";
        
    // }
// }







