<?php

function getTranslateInfo($keyword)
{
    if ($keyword == ""){
        return "要翻译的内容是什么";
    }
    $apihost = "http://fanyi.youdao.com/";
    $apimethod = "openapi.do?";
    $apiparams = array('keyfrom'=>"txw1958", 'key'=>"876842050", 'type'=>"data", 'doctype'=>"json", 'version'=>"1.1", 'q'=>$keyword);
    $apicallurl = $apihost.$apimethod.http_build_query($apiparams);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apicallurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	if(curl_errno($ch))
	{ echo 'CURL ERROR Code: '.curl_errno($ch).', reason: '.curl_error($ch);}
	curl_close($ch);

    $youdao = json_decode($output, true);
    $result = "";
	switch ($youdao['errorCode']){
		case 0:
			if (isset($youdao['basic'])){
				$result .= $youdao['basic']['phonetic']."\n";
				foreach ($youdao['basic']['explains'] as $value) {
					$result .= $value."\n";
				}
			}else{
				$result .= $youdao['translation'][0];
			}
			break;
		default:
			$result = "系统错误：错误代码：".$errorcode;
			break;
	}
    return trim($result);
}

?>