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
                $content = "欢迎关注应孙工作室 ";
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
        $category = substr($keyword,0,6);
        $code = trim(substr($keyword,6,strlen($keyword)));
        switch ($category)
        {
            case "股票":
                include("stock.php");
                $content = getStockInfo($code);
                break;
            case "分析":
                include("analysis.php");
                $content = getStockAnalysis($code);
                break;
            case "时间":
            	include("common.php");
            	$content = getDateTime();
            	break;
            case "头条":
                include("common.php");
                $content = getNews1();
                break;
            default:
                $content = "请输入：1）时间-获取当前时间；2）股票+股票代码-获取股票当前行情；3）分析+股票代码-获取股票分析；4）头条-获取网易头条新闻内容";
                break;
        }
        if(is_array($content)){
            $result = $this->transmitNews($object, $content);
        }else{
            /*if(substr($content, 0, 1) == "t"){
                $result = $this->transmitText($object, substr($content,1,strlen($content)-1));
            }elseif(substr($content, 0, 1) == "l") {
                $result = $this->transmitNews1($ojbect, $content);
                //$result = $this->transmitText($object, substr($content,1,strlen($content)-1));
            }else{
                $result = $this->transmitText($object, $content);
            }*/
            $result = $this->transmitText($object, $content);
        }
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

    /*private function transmitNews1($object, $content)
    {
        //$textTpl = "<xml>
//<ToUserName><![CDATA[%s]]></ToUserName>
//<FromUserName><![CDATA[%s]]></FromUserName>
//<CreateTime>%s</CreateTime>
//<MsgType><![CDATA[link]]></MsgType>
//<Title><![CDATA[%s]]></Title>
//<Description><![CDATA[%s]]></Description>
//<Url><![CDATA[%s]]></Url>
//</xml>";
        //$wopt = strpos($content, 'weixin');
        //$whref = substr($content, 1, strlen($content)-$wopt-9);
        //$wtitle = substr($content, $wopt+6, strlen($content)-$wopt-5);
        //$wtitle1 = $wtitle . '......';
        //$result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $wtitle, $wtitle1, $whref);
        //return $result;

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[link]]></MsgType>
<Title><![CDATA[测试连接]]></Title>
<Description><![CDATA[测试连接内容]]></Description>
<Url><![CDATA[http://news.163.com]]></Url>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }*/

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