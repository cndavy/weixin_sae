<?php


function getStockInfo($stockcode)
{
	if (!preg_match("/^\d{6}$/",$stockcode)){
		return "发送股票加上6位数字代码，例如“股票000063”";
	}
    $stockIndex = array(
      '999999' => 'sh000001',
      '399001' => 'sz399001',
      '000300' => 'sh000300',
      '399005' => 'sz399005',
      '399006' => 'sz399006',
      '000003' => 'sh000003'
    );
	if(array_key_exists($stockcode, $stockIndex)){
        $url = "http://hq.sinajs.cn/list=".$stockIndex[$stockcode];
	}else {
        $exchange = (substr($stockcode,0,1) < 5)?"sz":"sh";
        $url = "http://hq.sinajs.cn/list=".$exchange.$stockcode;
    }
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $result = iconv("GBK", "UTF-8//IGNORE", $data);

    $start = strpos($result,'"');   //第一次出现的位置
    $last  = strripos($result,'"'); //最后一次出现的位置
    $stockStr = substr($result, $start + 1, $last - $start - 1);
    $stockArray = explode(",",$stockStr);

    if (count($stockArray) <> 33){ return "不存在的股票代码？"; }

	$stockTitle = $stockArray[0]."[".$stockcode."]";
    $stockInfo = "最新：".$stockArray[3]."\n".
				 "涨跌：".round($stockArray[3]-$stockArray[2], 3)."\n".
				 "涨幅：".round(($stockArray[3]-$stockArray[2])/$stockArray[2]*100, 3)."%%\n".
                 "今开：".$stockArray[1]."\n".
                 "昨收：".$stockArray[2]."\n".
                 "最高：".$stockArray[4]."\n".
                 "最低：".$stockArray[5]."\n".
                 "总手：".
                    ((substr($stockcode,0,1) != 3)?
                        (array_key_exists($stockcode, $stockIndex)?round(($stockArray[8]/100000000),3)."亿":round(($stockArray[8]/1000000),3)."万")
                        :(array_key_exists($stockcode, $stockIndex)?round(($stockArray[8]/10000000000),3)."亿":round(($stockArray[8]/1000000),3)."万"))
                    ."\n".
				 "金额：".(array_key_exists($stockcode, $stockIndex)?round(($stockArray[9]/100000000),3)."亿":round(($stockArray[9]/10000),3)."万")."\n".
                 "更新：".$stockArray[30]." ".$stockArray[31];

	$resultArray = array();
	$resultArray[] = array(
                "Title" =>$stockTitle,
                "Description" =>$stockInfo,
                "PicUrl" =>"",
                "Url" =>"");
	
    return $resultArray;
}


?>