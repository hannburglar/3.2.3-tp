<?php
namespace Admin\Controller;
use Think\Controller;
class HomeController extends Controller {
    public function home(){
    	$cat = $this->selectCat();
    	$this->assign('cat',$cat);
    	$this->display('/Common/header');
        $this->display();
    }
	
	/*
	添加商品
	 */
	public function postt(){
		$Goods = M('Goods');
		$data = array();
		$data['cat_id']=I('data')[0];
		$data['goods_name']=I('data')[1];
		$goods_sn=I('data')[2];	//goods_sn
		$data['goods_sn']=$goods_sn;
		$data['goods_brief']=I('data')[3];
		$data['shop_price']=floatval(I('data')[4]);
		$data['goods_weight']=floatval(I('data')[5]);
		$data['goods_quantity']=I('data')[6];
		$data['goods_desc']=I('data')[7];
		$data['goods_yun']=I('data')[8];
		$time=time();	//add_time
		$data['add_time']=$time;
		if($Goods->add($data)==true){
			$good = $Goods->where("goods_sn='$goods_sn'")->field('goods_id,cat_id')->find();
			$goods_id=$good['goods_id'];
			$cat_id=$good['cat_id'];
			$tmp_name = $_FILES["goods_img"]["tmp_name"]; 
			$name = $_FILES['goods_img']['name'];
			$z = '/\.\w+/';		//匹配扩展名
			preg_match($z, $name, $ext);
			$ext=$ext[0];	//生成扩展名
			$uploads_dir = "D:/Apache24/htdocs/01MyShop/Public/images/goods_img/".$cat_id.'_'.$goods_id.'_'.$time.$ext; //图片路径
			if(move_uploaded_file($tmp_name,$uploads_dir)==true){		//移动图片到指定位置
				$imageSize = getimagesize($uploads_dir);
				// print_r($imageSize);
				$width = intval($imageSize[0]);
				$height = intval($imageSize[1]);
				$type = $imageSize[2];  //图片类型 1 = GIF，2 = JPG，3 = PNG，
				if($width>$height){		//根据谁比较长指定谁计算缩放比例
					$percent = 100/$width;
				}else{
					$percent = 100/$height;
				}
				if($percent<1){		//如果比例小于1,代表图像超过100 需要缩小
					$n_width = $width*$percent;
					$n_height = $height*$percent;
				}
				$thumb_dir = "D:/Apache24/htdocs/01MyShop/Public/images/goods_img/".$cat_id.'_'.$goods_id.'_'.$time.'_thumb'.$ext;		//缩略图路径
				$filename = $uploads_dir;
				$image_p = imagecreatetruecolor($n_width, $n_height);		//生成一个新的画布
				if($type==1){
					$image = imagecreatefromgif($filename);
				}
				if($type==2){
					$image = imagecreatefromjpeg($filename);
				}
				if($type==3){
					$image = imagecreatefrompng($filename);
				}
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $n_width, $n_height, $width, $height);
				if(imagejpeg($image_p, $thumb_dir, 100)==true){
					$pic = array();	//把图片路径存入数据库
					$pic['goods_img'] = "/01MyShop/Public/images/goods_img/".$cat_id.'_'.$goods_id.'_'.$time.$ext;
					$pic['thumb_img'] = "/01MyShop/Public/images/goods_img/".$cat_id.'_'.$goods_id.'_'.$time.'_thumb'.$ext;
					var_dump($Goods->where("goods_id='$goods_id'")->save($pic));
				}else{
					echo 'false3';
				}
			}else{
				echo 'false2';
			}		
		}else{
			echo 'false1';
		}
	}
	// 1_1031498630054_thumb_.jpg
	// 1_103_1498630054_thumb.jpg
	// /01MyShop/Public/images/goods_img/1_103_1498630054_thumb.jpg

	/*
	查询栏目
	 */
	public function selectCat(){
		$C = M('cat');
		return $C->select();
	}

	















}