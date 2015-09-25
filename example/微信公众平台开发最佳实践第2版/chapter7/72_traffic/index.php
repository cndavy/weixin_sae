<?php
/*
    方倍工作室
    CopyRight 2013 All Rights Reserved
*/

define("TOKEN", "weixin");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
	$wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;

            }
            $this->logger("T ".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
    
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注方倍工作室 ";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
        }
        $result = $this->transmitText($object, $content);
        return $result;
    }
  
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        $content[] = array("Title" =>"交通信息","Description" =>"", "PicUrl" =>"", "Url" =>"http://m.8684.cn/bus");
        $content[] = array("Title" =>"【公交线路】\n全车公交查询", "Description" =>"", "PicUrl" =>"http://photo.candou.com/ai/114/09caed4a27c56000bb870c68ab028850", "Url" =>"http://m.8684.cn/shenzhen_bus");
        $content[] = array("Title" =>"【汽车班次】\n长途汽车班车", "Description" =>"", "PicUrl" =>"http://photo.candou.com/i/175/4951bd2a2f368cafc5ad09ff95ce591d", "Url" =>"http://touch.trip8080.com/");
        $content[] = array("Title" =>"【火车时刻】\n火车时刻查询", "Description" =>"", "PicUrl" =>"http://photo.candou.com/ai/114/26a9407dcedda5f4a30b195f78ec3680", "Url" =>"http://m.ctrip.com/html5/Trains/");
        $content[] = array("Title" =>"【飞 机 票】\n机票查询", "Description" =>"", "PicUrl" =>"http://photo.candou.com/i/175/a1bd6303a8bde7da50166aa8dafb7568", "Url" =>"http://touch.qunar.com/h5/flight/");
        $content[] = array("Title" =>"【城市路况】\n重点城市实时路况", "Description" =>"", "PicUrl" =>"http://photo.candou.com/i/175/0d8488edf94574651d048025596a6190", "Url" =>"http://dp.sina.cn/dpool/tools/citytraffic/city.php");
        $content[] = array("Title" =>"【违章查询】\n全国违章查询", "Description" =>"", "PicUrl" =>"http://g.hiphotos.bdimg.com/wisegame/pic/item/9e1f4134970a304eab30503cd0c8a786c8175ce2.jpg", "Url" =>"http://app.eclicks.cn/violation2/webapp/index?appid=10");

        $result = $this->transmitNews($object, $content);
        return $result;
    }
    
    
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    private function transmitNews($object, $arr_item)
    {
        if(!is_array($arr_item))
            return;

        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
        return $result;
    }

    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_BAE_ENV_APPID'])){   //BAE
            require_once "BaeLog.class.php";
            $logger = BaeLog::getInstance();
            $logger ->logDebug($log_content);
        }else if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}


?>