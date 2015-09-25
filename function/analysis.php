<?php
// var_dump(getStockAnalysis("000063"));

function getStockAnalysis($stockcode)
{
	if (!preg_match("/^\d{6}$/",$stockcode)){
		return "发送分析加上6位数字代码，例如“分析000063”";
	}

	$resultArray = array();

    include_once('simple_html_dom.php');

	try {
		$url = "http://m.ghzq.cn/weixin/index.aspx?code=".$stockcode;
		$html_analysis = file_get_html($url);
		if (!isset($html_analysis)){
			$html_analysis->clear();
		}else{
            $stock = $html_analysis->find('div[class="row first"] div', 0)->plaintext;
            $resultArray[] = array(
                            "Title" =>trim($stock),
                            "Description" =>"",
                            "PicUrl" =>"",
                            "Url" =>"");
            //基本面
            $fundamentals = $html_analysis->find('div[class="font"]', 0);
            $resultArray[] = array(
                        "Title" =>str_replace("%", "%%", "【基本面】\n".$fundamentals->plaintext),
                        "Description" =>"",
                        "PicUrl" =>"",
                        "Url" =>"");
            //趋势面
            $technical = $html_analysis->find('div[class="font"]', 1);
            $resultArray[] = array(
                        "Title" =>str_replace("%", "%%", "【技术面】\n".$technical->plaintext),
                        "Description" =>"",
                        "PicUrl" =>"",
                        "Url" =>"");
            //评级面
            $technical = $html_analysis->find('div[class="font"]', 2);
            $resultArray[] = array(
                        "Title" =>str_replace("%", "%%", "【机构认同】\n".$technical->plaintext),
                        "Description" =>"",
                        "PicUrl" =>"",
                        "Url" =>"");
            $html_analysis->clear();
        }
	}catch (Exception $e){

	}
	return $resultArray;
}


?>