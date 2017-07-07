<?php
namespace Home\Controller;
use Think\Controller;
class ShopcartController extends Controller {
    	

	public function shopcart(){
		if(checkSession()==false){
			//用户没有登录重定向到登录页面
			redirect("../Login/login", 0.1, ' ');
		}else{
			//检测购物车有没有商品
			if($this->getCartCount()>0){
				$good=$this->shopcartContent(); 
				$this->assign('good',$good);
			}
			// print_r($good);
	        $this->display('/Common/header');
	        $this->display('/Common/search_nav');
	        $this->display();
	        $this->display('/Common/footer');
    	}
    }


    //获取购物车数据
    public function getCart(){
        $user_id=getUser();
        $Shopcart = D('Shopcart');
        return $Shopcart->where("user_id=$user_id")->select();
    }
	//组装sql的in语句
	public function sqlIn($name,$data){
		return $name.' in ('.implode(',',$data).')';
	}
    //获取用户购物车里全部的goods
    public function getGoodsFromCart(){
    	$user_id=getUser();
    	$cart=$this->getCart();
		$num=array();
		foreach ($cart as $k => $v) {
			$num[]=$v['goods_id'];
		}
		$name='shopcart.goods_id';
		$id = $this->sqlIn($name,$num);
		$Goods= D('Goods');
		$id = "user_id='$user_id' AND ".$id;
        return $Goods->join("shopcart on goods.goods_id = shopcart.goods_id")->where("$id")->select();
		// print_r($Goods->join("shopcart on goods.goods_id = shopcart.goods_id")->where("$id")->select());
    }
    
     //获取购物车商品数量
    public function getCartCount(){
        $user_id=getUser();
        $Shopcart = D('Shopcart');
        return $Shopcart->where("user_id=$user_id")->count();
    }

    //返回购物车数量给js
    public function reCartCount(){
        print_r($this->getCartCount());	
    }

     /*
    购物车内容放入缓存和输出,以及将标识存入缓存
     */
    public function shopcartContent(){
        $user_id = getUser();
        $sCartC = 'sCart-C'.$user_id;
        $shop =  useCache($sCartC,$user_id,'file');
        $sCartS = 'sCart-S'.$user_id;
        $sig =  useCache($sCartS,$user_id,'file');
        if($shop == ''|| $sig !== 'noChange'){
            $shop = $this->getGoodsFromCart();
            useCache($sCartC,$user_id,'file',$shop,900);
            $sign = 'noChange'; 
            useCache($sCartS,$user_id,'file',$sign,900);
        }
        return $shop;
    }  
    /*
    获取购物车内容输出json数据给js
     */
    public function reScartJsonToJs(){
        $sCart = $this->shopcartContent();
        static $i=0;
        foreach ($sCart as $k => $v) {
            $cart[$i]['goods_name']=$v['goods_name'];
            $cart[$i]['shop_price']=$v['shop_price'];
            $cart[$i]['quantity']=$v['quantity'];
            $cart[$i]['thumb_img']=$v['thumb_img'];
            $i++;
        }
        echo json_encode($cart);
        // print_r($cart);
    }




    /*
    增加商品到购物车
     */
    public function addToCart(){
    	if(IS_POST){
    		if(checkSession()==true){ 	//已登录,加入购物车
    			$user_id=getUser();
    			$data['user_id']=$user_id;
    			$goods_id=I('goods_id');
    			$data['goods_id']=$goods_id;
    			$quantity=I('quantity');
    			$data['quantity']=$quantity;
    			$Shopcart = D('Shopcart');
    			//检测购物车有没有该商品
    			$count=$Shopcart->where("user_id=$user_id and goods_id=$goods_id")->count();
    			if($count>0){ 	//购物车已有该商品
    				echo 'nono';
				}else{ 	//购物车没有该商品
                    changeScartSign(); //改变购物车缓存标识
    				echo $Shopcart->add($data)?'1111':'0000';
				}	
    		}
    	}
    }


    /*
    删除商品
     */
    public function deleteGoods(){
    	if(IS_POST){
    		$user_id = getUser();
            $S=M('shopcart');
            $shopcart_id=I('shopcart_id');
            $count=$S->where("shopcart_id=$shopcart_id AND user_id='$user_id'")->count(); //找查该商品条数
            if($count>0){
                changeScartSign(); //改变购物车缓存标识
                $S->where("shopcart_id=$shopcart_id AND user_id='$user_id'")->delete();   //执行删除商品
                echo true;
            }else{
                echo false;     //商品在执行操作前已被删除
            }
        }else{
            echo  false;      // 没有post数据
        }
    }

    /*
    修改数量
     */
    public function oook(){
        echo 'ok';
    }
    public function modifyQuantity(){
        if(IS_POST){
            $data=array();
            $shopcart_id=(I('shopcart_id'));
            $data['quantity']=(I('quantity'));
            changeScartSign(); //改变购物车缓存标识          
            $S=M('shopcart');
            echo $S->where("shopcart_id=$shopcart_id")->save($data)?true:false;
        }else{
            echo  false;    // 没有post数据
        }
    }
    
    /**
     * 根据购物车id获取选中的购物车商品
     * @param  [type] $c [购物车id]
     */
    public function getCheckedGoods($c){    
        //重排数组下标
        $d = array_merge($c);
        $shopcart = $this->getGoodsFromCart();
        $confirm=array();
        for($i=0;$i<count($d);$i++){
            foreach ($shopcart as $k => $v) {
                if($v['shopcart_id']==$d[$i]){
                    $confirm[$i]=$v;
                }
            }
        }           
        return $confirm;    
    }


    public function goToConfirm(){
        $confirm =$this->getCheckedGoods();
        if($confirm==null){
            echo 'null';
        }else{
            print_r($confirm);
        }
    }




























}