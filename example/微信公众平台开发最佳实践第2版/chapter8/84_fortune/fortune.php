<?php


function getFortuneInfo($keyword)
{
    if ($keyword == ""){
        return "请发送算命+人名，例如“算命张三”";
    }
    if ((strlen($keyword) < 6) || (strlen($keyword) > 12)){
        return "人名长度为2到4个汉字";
    }
    try {
        include('simple_html_dom.php');
        $url = "http://m.1518.com/xingming_view.php?word=".urlencode(mb_convert_encoding($keyword, 'gb2312', 'utf-8'))."&submit1=%C8%B7%B6%A8&FrontType=1";
		$html_fotune = file_get_html($url);
		if (!isset($html_fotune)){
			$html_fotune->clear();
			return "程序检索出错！\n如果经常这样，请联系方倍工作室。";
		}
        $infomation = "";
        foreach($html_fotune->find('div[id="detail"] dl') as $item) {
            $curText = $item->plaintext;
            $curText = preg_replace('/\s{2,}/i', ' ', $curText);
            $curText = preg_replace('/\t{2,}/i', ' ', $curText);
            $curText = preg_replace('/：\s/i', '：', $curText);
            $infomation .= trim($curText)."\n";
            if (strlen($infomation) > 2000){break;}
        }
        $html_fotune->clear();
        $infomation = str_replace("\r\n", "\n", $infomation);
        $infomation = str_replace("天格", "\n天格", $infomation);
        $infomation = str_replace("　", "", $infomation);
        return trim($infomation);
	}catch (Exception $e){

	}
}
?>