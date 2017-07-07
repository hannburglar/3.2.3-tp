<?php  

/*
md5加密
 */
function encrypt($a){
	return md5($a);
}


// function cookieEncrypt(){
// 	// echo cookie('user').cookie('pwd').C('COOKIE_KEY');
// 	// echo "<br>";
// 	// echo  cookie('key');
// 	return encrypt(cookie('user').cookie('pwd').C('COOKIE_KEY')) == cookie('key');
// }

/**
 * 检测有没有session
 */
function checkSession(){
    if(!empty(session('user'))){
    	return true;
    }else{
    	return false;
    }
}
/*
获取session用户id
 */
function getUser(){
  return session('u_id');
}

/**
   * 设置缓存
   * @param  [type] $name   [缓存名字]
   * @param  string $temp   [文件夹名称]
   * @param  [type] $data   [存入缓存的数据]
   * @param  string $type   [缓存类型 file memcached 等等]
   * @param  int    $expire [有效期 毫秒]
   */
 function useCache($name,$temp='',$file='',$data,$expire=''){
    if(!empty($type)){
      $arr['type'] = $type;
    }
    if(!empty($temp)){
      $arr['temp'] = RUNTIME_PATH."temp/".$temp;
    }
    if(!empty($expire)){
      $arr['expire'] = $expire;
    }
    if(!empty($data)){  
      $cache = S($arr);
    return $cache->$name=$data; //有 $data 表示要存入缓存
    }else{  
      $cache = S($arr);
    return $cache->$name;   // 没有 $data 表示要取出缓存结果
    }
  } 

  /*
    改变购物车缓存标识
   */
  function changeScartSign(){
    $user_id = getUser();
    $sCartS = 'sCart-S'.$user_id;
    $sign = 'Changed'; 
    useCache($sCartS,$user_id,'file',$sign,900);
  }
