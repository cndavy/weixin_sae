<?php
define("TOKEN", "weixin");
define("APPLOCATION", "http://18607110495.sinaapp.com");

//引入微信高级接口类
include("./function/weixin.class.php");
$wxclass = new class_weixin_adv("wx551811ed349b6325","96e4c8eedfff806456b942e170dd43b7");

$jsonmenu = '{
      "button":[
      {
           "name":"我的订单",
           "sub_button":[
            {
               "type":"click",
               "name":"采购货品",
               "key":"采购货品"
            },
            {
               "type":"click",
               "name":"库存查询",
               "key":"库存查询"
            },
            {
               "type":"click",
               "name":"物流查询",
               "key":"物流查询"
            }]
       },
       {
           "name":"促销信息",
           "sub_button":[
            {
               "type":"click",
               "name":"最新促销",
               "key":"最新促销"
            }]
       },
       {
           "name":"相关服务",
           "sub_button":[
            {
               "type":"click",
               "name":"网易头条",
               "key":"网易头条"
            },
            {
                "type":"view",
                "name":"掌上百度",
                "url":"http://m.baidu.com"
            },
            {
                "type":"location_select",
                "name":"附近加油",
                "key":"附近加油"
            }]
       }]
 }';

//创建自定义菜单代码结束
var_dump($wxclass->create_menu($jsonmenu));


$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();

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
            ob_clean();
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
            //---响应自定义菜单代码开始---
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "网易头条":
                        include("./function/common.php");
                        $content = getNews1(APPLOCATION);
                        break;
                    default:
                        $content = "你点击了 " . $object->EventKey . "。";
                        break;
                }
                break;
            case "LOCATION":
                include("./function/common.php");
                $content = catchEntitiesFromLocation($object->Latitude,$object->Longitude);
                break;
            //---响应自定义菜单代码结束---
        }
        if(is_array($content)){
            $result = $this->transmitNews($object, $content);
        }else{
            $result = $this->transmitText($object, $content);
        }
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
                include("./function/stock.php");
                $content = getStockInfo($code);
                break;
            case "分析":
                include("./function/analysis.php");
                $content = getStockAnalysis($code);
                break;
            case "时间":
            	include("./function/common.php");
            	$content = getDateTime();
            	break;
            case "头条":
                include("./function/common.php");
                $content = getNews1(APPLOCATION);
                break;
            //case "位置"
            //    include("./function/common.php");
            //    $content = getGeo($object->Latitude, $object->Longitude);
            //    break;
            case "星座":
                include("./function/common.php");
                $content = getAstrologyInfo($code);
                break;
            case "翻译":
                include("./function/common.php");
                $content = getTranslateInfo($code);
                break;
            default:
                $content = "请输入：1）时间-获取当前时间；2）股票+股票代码-获取股票当前行情；3）分析+股票代码-获取股票分析；4）头条-获取网易头条新闻内容；5）翻译+内容-可以对内容进行中译英和英译中的翻译，有道翻译提供支持；6）星座+星座名称-例如星座双子，可以获取双子座的运势，新浪星座提供支持";
                break;
        }
        if(is_array($content)){
            $result = $this->transmitNews($object, $content);
        }else{
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