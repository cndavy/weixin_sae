<?php

include('simple_html_dom.php');
 
// get DOM from URL or file
$html = file_get_html('http://news.163.com/');
//echo "start!" . '<br>';
//$imgurl = '123';

//$resultArray = array();
                
// find all link
foreach($html->find('h3') as $e)
    foreach($e->find('a') as $f1)
        if(substr($f1->href, 7, 4) == "news"){
            $htmlimg = file_get_html($f1->href);
            foreach ($htmlimg->find('img') as $f2)
            	if($f2->id <> ''){
            		echo $f2->src .'<br>';
            	}else{
            		echo 'abcd' . '<br>';
            	}
            	//echo $f2->id. '&nbsp;&nbsp;';
                //echo $f2->src . '<br>';
        }
                
//echo $imgurl;
//echo "stop!";
?>