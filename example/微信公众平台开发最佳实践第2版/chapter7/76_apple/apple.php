<?php
// var_dump(getAppleInfo("358843053134475"));  // 5s
// var_dump(getAppleInfo("358031058974471"));  //5c
// var_dump(getAppleInfo("F17JTV98DTWD"));  //5
// var_dump(getAppleInfo("dmrhc75ldfhw"));  //iPad 2
function getAppleInfo($sn)
{
    $rnd = rand(100,999999);
    $post = "sn=$sn&cn=&locale=&caller=&num=$rnd"; 
    $url = "https://selfsolve.apple.com/wcResults.do";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result=curl_exec($ch);
    curl_close($ch);

    $result = str_replace("'","",$result);
    preg_match('#setClassAndShow\((.*?)\)#is',          $result, $RegistrationInfo);//注册信息
    preg_match('#displayProductInfo\((.*?)\)#is',       $result, $ProductInfo);     //产品信息
    preg_match('#displayPHSupportInfo\((.*?)\)#is',     $result, $PHSupportInfo);   //电话支持
    preg_match('#displayHWSupportInfo\((.*?)\)#is',     $result, $HWSupportInfo);   //硬件保修

    if (empty($RegistrationInfo)){
        return "很抱歉，此序列号无效。请检查您的信息，然后再试。";
    }
    
    $registration = explode(",",$RegistrationInfo[1]);
    $product = explode(",",$ProductInfo[1]);
    $phsupport = explode(",",$PHSupportInfo[1]);
    $hwsupport = explode(",",$HWSupportInfo[1]);

    $phsupport_date = "";   //提取电话支持日期
    if (trim($phsupport['0']) == "true"){
        preg_match('#Estimated Expiration Date:(.*?)<br/>#is', $phsupport['2'].$phsupport['3'], $phsupport_date); 
    }
    $hwsupport_date = "";   //提取保修服务日期
    if (trim($hwsupport['0']) == "true"){
        preg_match('#Estimated Expiration Date:(.*?)<br/>#is', $hwsupport['2'].$hwsupport['3'], $hwsupport_date); 
    }

    $title = "苹果产品信息查询";
    $description = "设备名称：".trim($product['1'])."\n".
    ((preg_match("/^\d{8,20}$/",$sn))?"IMEI号":"序列号")."：".$product['3']."\n".
    "购买日期：".((trim($registration['2']) == "registration-true")?"已验证":"无效")."\n".
    "电话支持：".((trim($phsupport['0']) == "true")?"有效[".trim($phsupport_date['1'])."]":"已过期")."\n".
    "保修服务：".((trim($hwsupport['0']) == "true")?"有效[".trim($hwsupport_date['1'])."]":"已过期")."\n".
    "\n数据来自苹果公司官方网站";
    $picurl = $product['0'];
    
    $months = array(
        "01-"=>"January ", "02-"=>"February ", "03-"=>"March ", 
        "04-"=>"April ", "05-"=>"May ", "06-"=>"June ",
        "07-"=>"July ", "08-"=>"August ", "09-"=>"September ",
        "10-"=>"October ", "11-"=>"November ", "12-"=>"December ",
    );
    foreach($months as $key=>$value){
        $description = str_ireplace($value, $key, $description);
    }

    $result = array(); 
    $result[] =  array(
        "Title"=>$title,
        "Description" =>trim($description),
        "PicUrl" =>$picurl,
        "Url" =>""
    );
    return $result;
}


?>