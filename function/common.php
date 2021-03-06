<?php


function getDateTime()
{
	//$commonTitle = "当前时间——"
	//$commonContent = date("Y-m-d H:i:s",time());

	//$resultArray = array();
	//$resultArray[] = array(
    //            "Title" =>$commonTitle,
    //            "Description" =>$commonContent,
    //            "PicUrl" =>"",
    //            "Url" =>"");
	
    //return $resultArray;
    //$commonContent = 't' . date("Y-m-d H:i:s",time());
    $commonContent = date("Y-m-d H:i:s", time());
    return $commonContent;
}

function getNews()
{
    include('simple_html_dom.php');
 
    // get DOM from URL or file
    $html = file_get_html('http://news.163.com/');

    // find all link
    foreach($html->find('h3') as $e)
        foreach($e->find('a') as $f1)
            if(substr($f1->href, 7, 4) == "news"){
                for($i=0; $i<1; $i++){
                    return 'l' . $f1->href . 'weixin' . $f1->plaintext;
                }
            }else{
                return "";
            }           
}

function getNews1($applocation)
{
    include('simple_html_dom.php');
 
    // get DOM from URL or file
    $html = file_get_html('http://news.163.com/');
    $imgurl = '';

    $resultArray = array();
                
    // find all link
    foreach($html->find('h3') as $e)
        foreach($e->find('a') as $f1)
            if(substr($f1->href, 7, 4) == "news"){
                //$content =  $f1->href . 'weixin' . $f1->plaintext;
                //$wopt = strpos($content, 'weixin');
                //$whref = substr($content, 1, strlen($content)-$wopt-7);
                //$wtitle = substr($content, $wopt+6, strlen($content)-$wopt-5);
                //$wtitle1 = $wtitle . '......';
                //$htmlimg = file_get_html($f1->href);
                //foreach ($htmlimg->find('img') as $f2) {
                //	for(int i=0;i<=1;i++){
                //		$imgurl = $f2->href;
                //	}
                //}
                $resultArray[] = array(
                    "Title" =>$f1->plaintext,
                    "Description" =>"",
                    "PicUrl" =>$applocation . "/pic/news.png",
                    //"PicUrl" =>$imgurl,
                    "Url" =>$f1->href);
            }

    return $resultArray;           
}

function getGeo($Latitude, $Longitude){
    $url = "http://api.map.baidu.com/geocoder/v2/?ak=B944e1fce373e33ea4627f95f54f2ef9&location=$Latitude,$Longitude&output=json&coordtype=gcj02ll";
    $output = file_get_contents($url);
    $address = json_decode($output, true);
    $contentStr = "位置 ".$address["result"]["addressComponent"]["province"]." ".$address["result"]["addressComponent"]["city"]." ".$address["result"]["addressComponent"]["district"]." ".$address["result"]["addressComponent"]["street"];

    return $contentStr;
}

function getAstrologyInfo($entity)
{
    $capitals = array(
        '白羊' => '1',
        '金牛' => '2',
        '双子' => '3',
        '巨蟹' => '4',
        '狮子' => '5',
        '处女' => '6',
        '天秤' => '7',
        '天蝎' => '8',
        '射手' => '9',
        '摩羯' => '10',
        '水瓶' => '11',
        '双鱼' => '12'
    );
    if (!array_key_exists($entity, $capitals))
    {
        return "星座名只有以下这些：\n白羊座 金牛座 双子座 巨蟹座 狮子座 处女座 天秤座 天蝎座 射手座 摩羯座 水瓶座 双鱼座";
    }
    $astrologyArray[] = array(
    "Title" =>$entity."运势", 
    "Description" =>"", 
    "PicUrl" =>"http://pic14.nipic.com/20110519/2457331_223610757000_2.jpg", 
    "Url" =>"http://dp.sina.cn/dpool/astro/starent/starent.php?type=day&ast=".$capitals[$entity]."&vt=4");

    return $astrologyArray;
}

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

//附近加油站
function catchEntitiesFromLocation($x, $y)
{
    $url = "http://api.map.baidu.com/place/v2/search?ak=MgBALVVeCd8THVBi6gPdvsvG&output=json&query=加油站&page_size=5&page_num=0&scope=2&location=".$x.",".$y."&radius=5000&filter=sort_name:distance";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    
    $data = json_decode($output, true);

    if ($data['status'] != 0){
        return $data['message'];
    }
    
    $results = $data['results'];
    if (count($results) == 0){
        return "附近没有找到加油站";
    }
    $shopArray = array();
    $shopArray[] = array("Title"=>"附近的加油站", "Description"=>"", "PicUrl"=>"", "Url"=>"");
    for ($i = 0; $i < count($results); $i++) {
        $shopArray[] = array(
            "Title"=>"【".$results[$i]['name']."】<".$results[$i]['detail_info']['distance']."米>\n".$results[$i]['address'].
            (isset($results[$i]['telephone'])?"\n".$results[$i]['telephone']:""),
            "Description"=>"", 
            "PicUrl"=>"", 
            "Url"=>(isset($results[$i]['detail_info']['detail_url'])?($results[$i]['detail_info']['detail_url']):""));
    }
    return $shopArray;
}

?>