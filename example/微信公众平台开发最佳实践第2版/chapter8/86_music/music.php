<?php
// var_dump(getMusicInfo("凤凰传奇@最炫民族风"));
function getMusicInfo($entity)
{
	if ($entity == ""){
		$content = "你还没告诉我音乐名称呢？";
	}
    else{
        if (strpos($entity, "@")){
            $music = explode("@",$entity);
            $url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=".$music[1]."$$".$music[0]."$$$$";
        }else{
            $url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=".$entity."$$";
        }

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
        
		$content = "检索失败";
		try{
			@$menus = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($menus->count > 0 && isset($menus->url[0]) && isset($menus->durl[0])){
                $url_prefix = substr($menus->url[0]->encode,0,strripos($menus->url[0]->encode,'/') + 1);
                $url_suffix = substr($menus->url[0]->decode,0,strripos($menus->url[0]->decode,'&'));
                $durl_prefix = substr($menus->durl[0]->encode,0,strripos($menus->durl[0]->encode,'/') + 1);
                $durl_suffix = substr($menus->durl[0]->decode,0,strripos($menus->durl[0]->decode,'&'));
                if (strpos($entity, "@")){
                    $content = array( "Title"=>$music[1],
                                    "Description"=>$music[0],
                                    "MusicUrl"=>$url_prefix.$url_suffix,
                                    "HQMusicUrl"=>$durl_prefix.$durl_suffix);
                }else{
                    $content = array( "Title"=>$entity,
                                    "Description"=>"百度音乐掌门人提供",
                                    "MusicUrl"=>$url_prefix.$url_suffix,
                                    "HQMusicUrl"=>$durl_prefix.$durl_suffix);
                }
            }
		}catch(Exception $e){
		}
	}
	return $content;
}

?>