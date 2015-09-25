﻿<?php
//var_dump(getRecognizeInfo("http://img3.yxlady.com/yl/UploadFiles_5361/20110513/20110513130615793.jpg"));
function getImageInfo($url)
{
	$faceObj = new FacePlusPlus();
	$detect = $faceObj->face_detect($url);
    $numbers = isset($detect->face)? count($detect->face):0;
    if (($detect->face[0]->attribute->gender->value != $detect->face[1]->attribute->gender->value) && $numbers == 2){
        $compare = $faceObj->recognition_compare($detect->face[0]->face_id,$detect->face[1]->face_id);
        $result = getCoupleComment($compare->component_similarity->eye, $compare->component_similarity->mouth, $compare->component_similarity->nose, $compare->component_similarity->eyebrow, $compare->similarity);
        return $result;
    }else{
        return "似乎不是一男一女，无法测试夫妻相";
    }
}

function getCoupleComment($eye, $mouth, $nose, $eyebrow, $similarity)
{
    $index = round(($eye + $mouth + $nose + $eyebrow) / 4);
    if ($index < 40){
        $comment = "花好月圆";
    }else if ($index < 50){
        $comment = "相濡以沫";
    }else if ($index < 60){
        $comment = "情真意切";
    }else if ($index < 70){
        $comment = "郎才女貌";
    }else if ($index < 80){
        $comment  = "心心相印";
    }else if ($index < 90){
        $comment  = "浓情蜜意";
    }else{
        $comment = "海盟山誓";
    }
    return "【夫妻相指数】\n得分：".$index."\n评语：".$comment;
}

class FacePlusPlus
{
	private $api_server_url;
	private $auth_params;

	public function __construct()
	{
		$this->api_server_url = "http://apicn.faceplusplus.com/";
    	$this->auth_params = array();
   		$this->auth_params['api_key'] = "";
   		$this->auth_params['api_secret'] = "";
	}

    //人脸检测
	public function face_detect($urls = null)
	{
		return $this->call("detection/detect", array("url"=>$urls));
	}

	//人脸比较
	public function recognition_compare($face_id1, $face_id2)
	{
		return $this->call("recognition/compare", array("face_id1"=>$face_id1, "face_id2"=>$face_id2));
	}

    protected function call($method, $params = array())
    {
        $url = $this->api_server_url."$method?".http_build_query(array_merge($this->auth_params, $params));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	$data = curl_exec($ch);
    	curl_close($ch);
        $result = json_decode($data);
		return $result;
    }
}
?>

