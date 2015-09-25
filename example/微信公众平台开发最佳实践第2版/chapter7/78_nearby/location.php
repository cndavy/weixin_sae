<?php

/*
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `openid` varchar(28) NOT NULL COMMENT '微信ID',
  `locationX` float default '0' COMMENT '纬度',
  `locationY` float default '0' COMMENT '经度',
  PRIMARY KEY (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;
*/


/*
{"ollB4jtmI_i8CqYlj-QMiuxx": {"locationX": "22.123","locationY": "113.23434"}}


var_dump($l);
var_dump($l["locationX"]);

setLocation("ollB4jtmI_i8CqYlj-QMiuxx", "22.123", "113.23434");
$l = getLocation("ollB4jtmI_i8CqYlj-QMiuxx");
var_dump($l);

setLocation("oDeOAjgSJUX10wvImSRMSwmyQAyA", "22.123", "113.23434");
$location = getLocation("oDeOAjgSJUX10wvImSRMSwmyQAyA");
var_dump($location);
*/
function setLocation($openid, $locationX, $locationY)
{
    $mmc = memcache_init();
    if($mmc == true){
        $location = array("locationX"=>$locationX, "locationY"=>$locationY);
        memcache_set($mmc, $openid, json_encode($location), 60);
        return "您的位置已缓存。\n现在可发送“附近”加目标的命令，如“附近酒店”，“附近加油站”。";
    }
    else{
        return "未启用缓存，请先开启服务器的缓存功能。";
    }
}

function getLocation($openid)
{
    $mmc = memcache_init();
    if($mmc == true){
        $location = memcache_get($mmc, $openid);
        if (!empty($location)){
            return json_decode($location,true);
        }else{
            return "请先发送位置给我！\n点击底部的'+'号，再选择'位置'，等地图显示出来以后，点击'发送'";
        }
    }
    else{
        return "未启用缓存，请先开启服务器的缓存功能。";
    }
}

?>