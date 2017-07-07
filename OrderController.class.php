<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller {
    
    /*
    订单代付款页面
     */	
	public function order_unpaid(){
		if(checkSession()==false){	
			redirect("../Login/login", 0.1, ' ');
        }else{	
        	if(IS_GET){
        		$sn = I('sn');
        		$order = $this->selectOrderBothBySn($sn);
        		// print_r($order);
        		// print_r($this->closeOrder($sn));	  
        		$this->assign('order',$order);	
        		$time = $order[0]['create_time'];
        		$date = date('Y-m-d G:i:s',$time);
        		$this->assign('date',$date);	
        	}
			$this->display('/Common/header');
	        $this->display();
	        $this->display('/Common/footer');
        	
	    }
	}

	/*
	根据order_sn号同时查询两个order表
	 */
	public function selectOrderBothBySn($sn){
		$OrderG=D('Order_glancing');
		// $OrderD=D('Order_detail');
		return $OrderG->join("Order_detail on order_glancing.order_sn = order_detail.order_sn")->where("order_glancing.order_sn='$sn'")->select();	
	}

	/*
	接收数据,同时生吃订单,删除购物车提交后的商品
	 */
	public function getPost(){
		if(checkSession()==false){	
			redirect("../Login/login", 0.01, '');
        }else{	
			if (IS_POST) {
				$message = I('message');
				$mem=A('Member');
				$ad = $mem->getDefAddress();
				$address = $ad['ad_consignee'].','.$ad['ad_phone'].','.$ad['ad_address'];
				$sn = $this->getOrderSn();
				$json = $_POST['json'];
				if($this->insertOrderGl($sn,$address,$message)){
					if($this->insertOrderDe($sn,$json)){
						$this->setOrderStateTwo($sn);		
						redirect('order_unpaid?sn='.$sn, 0.01, '页面跳转中...');
					}
				}
			}		
		}
	}

	/**
	 * 唯一订单号
	 */
	public function getOrderSn(){
		$data=getUser();
		$data .=$_SERVER['REQUEST_TIME_FLOAT'];
		$data .= $_SERVER['REMOTE_ADDR'];
		$hash =hash('ripemd320', md5($data));
		$reg = '/\d/';
		preg_match_all($reg,$hash,$e);
		$rand=rand(0,11);
		$f =implode('',$e[0]);
		return substr($f,$rand,19);
	}

	/*
	创建订单主表
	 */
	public function insertOrderGl($sn,$address,$message){
		$data=array();
		$data['user_id'] = getUser();
		$data['order_sn'] = $sn;
		$data['create_time'] = $_SERVER['REQUEST_TIME'];
		$data['order_state'] = '1';
		$data['order_address'] = $address;
		$data['order_message'] = $message;
		$OrderG=D('Order_glancing');
		return $OrderG->add($data)?ture:false;		
	}

	/*
	根据购物车id获取goods
	 */
    public function getGoodsFromSId($shopcart_id){
    	$Shopcart=D('Shopcart');
    	$res = $Shopcart->where("shopcart_id='$shopcart_id'")->field('goods_id,quantity')->find();
    	return $res;
    }

	/*
	创建订单明细表
	 */
	public function insertOrderDe($sn,$json){
		$data = json_decode($json, true);
		$be=array();
		$good=array();
		foreach ($data as $k => $v) {
			$da=array(); 
			$da['order_sn'] = $sn;
			$vv = $this->getGoodsFromSId($v);
			$da['order_quantity'] = $vv['quantity'];
			$da['goods_id'] = $vv['goods_id'];
			//组成数组
			$be[] = $da;
			$good[]=$vv['goods_id'];
		}
		//组装sql语句
		$name='goods_id';
		$Shopcart=A('Shopcart');
		$id = $Shopcart->sqlIn($name,$good);
		//取得商品信息
		$Goods=D('Goods');	
		$d = $Goods->where("$id")->field("goods_name,shop_price,thumb_img")->select();
		//将商品新放入待插入的数组
		$order=array();
		for($i=0;$i<count($be);$i++){
			$order[] = array_merge($be[$i],$d[$i]); 	
		}
		$OrderD=D('Order_detail');
		return $OrderD->addAll($order)?ture:false;		
	}

	/*
 	定时设定订单状态为2,即是关闭订单
 	 */
 	public function setOrderStateTwo($sn){
 		// $sn = 456453453;
 		$M = M();
 		$sql = "create event myevent".$sn; 
 		$sql .= " on schedule at current_timestamp + interval 900 second "; 
 		$sql .= " do";
 		$sql .= " update shop.order_glancing set order_state = 0 where order_sn = ".$sn.";";
 		// $sql ="select * from order_glancing";
 		// echo $sql;
 		return $M->execute($sql);
 	}


 	/*
 	取消定时设定订单状态
 	 */
 	public function dropEvent($sn){
 		$M = M();
 		$sql = "drop event myevent".$sn; 
 		return $M->execute($sql);
 	}

 	/*
 	关闭订单
 	 */
 	public function closeOrder(){
 		if(IS_POST){
 			$order_sn = I('order_sn');
 			$data = array();
 			$data['order_state'] = '0';
 			$orderG = M('Order_glancing');
 			print_r($orderG->where("order_sn='$order_sn'")->save($data));
 		}
 	}
	/*
	订单已关闭页面
	 */
	public function order_closed(){
		if(checkSession()==false){	
			redirect("../Login/login", 0.1, ' ');
        }else{	
        	if(IS_GET){
        		$sn = I('sn');
        		$order = $this->selectOrderBothBySn($sn);
        		// print_r($order);
        		// print_r($this->closeOrder($sn));	  
        		$this->assign('order',$order);	
        		$time = $order[0]['create_time'];
        		$date = date('Y-m-d G:i:s',$time);
        		$this->assign('date',$date);	
        	}
			$this->display('/Common/header');
	        $this->display();
	        $this->display('/Common/footer');
        	
	    }
	}

	

	// /*
	// 查询订单glancing
	//  */
	// public function findOrderGl($sn){
	// 	$order_sn=$sn;
	// 	$OrderG=D('Order_glancing');
	// 	return $OrderG->where("order_sn='$order_sn'")->find();		
	// }
	// /*
	// 查询订单detail
	//  */
	// public function selectOrderDe($sn){
	// 	$order_sn=$sn;
	// 	$OrderD=D('Order_detail');
	// 	return $OrderD->where("order_sn='$order_sn'")->select();		
	// }
	

	

	


	
	















	/*
	定时器
	 */
	// public function timer(){
	// 	ignore_user_abort();//当用户关闭页面时服务停止
	// 	set_time_limit(60);  //设置执行时间，单位是秒。0表示不限制。
	// 	// date_default_timezone_set('Asia/Shanghai');//设置时区
	// 	$interval=10;
	// 	$Add=D('Address');
	// 	$data=array();
	// 	$data['user_id']=10;
	// 	$data['ad_address']='哈哈哈哈';
	// 	$data['ad_consignee']='肯德基';
	// 	$data['ad_phone']=time();
	// 	$data['ad_default']=0;
	// 	while(ture){
	// 		$Add->add($data);
	// 	    //这里是需要定时执行的任务
	// 	    sleep($interval);//暂停时间（单位为秒）

	// 	}
	// }

}