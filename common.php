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

function getNews1()
{
    include('simple_html_dom.php');
 
    // get DOM from URL or file
    $html = file_get_html('http://news.163.com/');

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

                $resultArray[] = array(
                    "Title" =>$f1->plaintext,
                    "Description" =>"",
                    "PicUrl" =>"",
                    "Url" =>$f1->href);
            }

    return $resultArray;           
}

?>