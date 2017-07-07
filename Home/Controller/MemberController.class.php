<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller {
    
   /**
    * 会员 订单 页面
    */
    public function member_order(){
    	if(checkSession()==false){	
			redirect("../Login/login", 0.1, ' ');
        }else{	
        	//当前第几页
    		$ye = I('page')?I('page'):1;
    		$op = $this->orderPage($ye,$mc=8);
        	$page=$op['page'];
        	$this->assign('page',$page);
        	$order=$op['order'];
        	$this->assign('order',$order);
            $this->display('/Common/header');
            $this->display('/Common/search_nav');
            $this->display();
            $this->display('/Common/footer');
        }
    }
    /*
    返回 分页 和 分页内容数组
    $ye[当前第几页],$mc[每页订单数量]
     */
    function orderPage($ye,$mc=''){
    	$user_id = getUser(); //用户id
	    	$orderName ='order_page'.$user_id;	//订单内容缓存名字
	    	$cache = useCache($orderName,$user_id,'file');	//将订单取出缓存
    	$countName ='order_count'.$user_id; //订单数量缓存名字
    	$cacheCount = useCache($countName,$user_id,'file');	//获取缓存订单数量
    	$ordCount = $this->orderGlCount();	//查询订单数量

    	if($cache == ""||$cacheCount!==$ordCount){	//如果订单缓存为空或者数量不相等    		
    		$order = $this->restructureOrder();		//获取订单内容数组
    		useCache($orderName,$user_id,'file',$order,900);	//订单内容存入缓存   		
    		$count = count($order);
    		useCache($countName,$user_id,'file',$count,900);	//订单条数存入缓存
    	}else{
    		$order = $cache;
    	}	
    	$count = count($order);		//总共订单数量
    	$pc = ceil($count/$mc); 		//总共需要多少页
    	$n=$mc*($ye-1);		//每页从第几个订单开始取
    	$order = ($order,$n,$mc);	//每页显示
    	for ($i=1; $i <=$pc ; $i++) { 		//生成页面数组
   			$page[] .="<a href='./member_order?page=".$i."' class='order_page_div_a'>".$i."</a>&nbsp&nbsp";
    	}
    	if($ye>1){		//显示向回按钮
    		$i=$ye-1;
    		$back = "<a href='./member_order?page=".$i."'> << </a>&nbsp&nbsp";
    		array_unshift($page,$back);
    	}
    	if($ye<$pc&&$pc>1){		//显示向前按钮
    		$i=$ye+1;
    		$front = "<a href='./member_order?page=".$i."'> >> </a>&nbsp&nbsp";
    		array_push($page,$front);
    	}
    	return array('order'=>$order,'page'=>$page);	//返回订单内容和分页
    }
   
    /*
	同时查询两个order表
	 */
	public function selectOrderBoth(){
		$user_id = getUser();
		$OrderG=D('Order_glancing');
		// $OrderD=D('Order_detail');
		return $OrderG->join("Order_detail on order_glancing.order_sn = order_detail.order_sn")->order('create_time desc')->where("user_id='$user_id'")->select();	
	}
	/*
	查询订单glancing数量
	 */
	public function orderGlCount(){
		$user_id = getUser();
		$OrderG=D('Order_glancing');
		return $OrderG->where("user_id='$user_id'")->count();		
	}

	/*
	调整订单数组的结构
	 */	
	public function restructureOrder(){
		$order = $this->selectOrderBoth();
		$gla =array();
		$det = array();
		for ($i=0; $i <count($order); $i++) { 
			//统一个订单号的detail部分放入 det 数组储存
			$part = array_slice($order[$i], 6); 
			$part['order_sn']=$order[$i]['order_sn'];

			$det[]=$part;
			//去掉订单detail部分
 			array_splice($order[$i], 6);
 			//该订单数组 sn 号 不等于 下一个订单数组 sn 号,表示该订单查找完毕
 			if($order[$i]['order_sn'] !== $order[$i+1]['order_sn']){
				//时间戳转换成时间
				$time = $order[$i]['create_time'];
				$order[$i]['create_time']=date('Y-m-d G:i:s',$time);
 				//将detail 部分放入 order_detail元素
 				$order[$i]['order_detail']=$det;
 				//该订单有多少个商品
 				$order[$i]['goods_count']=count($det);
 				//清空 det 数组
 				$det=array();
 				//放入 gla 数组储存
 				$gla[] = $order[$i];
 			}
		}
		return $gla;	
	}



    /**
     * 会员 地址 页面 
     */
	public function member_address(){
    	if(checkSession()==false){
			redirect("../Login/login", 0.1, ' ');
        }else{

        	$address=$this->getAddress();
        	$this->assign('address',$address);

        	$count=$this->addressCount();        
        	$this->assign('count',$count);
        	
            $this->display('/Common/header');
            $this->display('/Common/search_nav');
            $this->display();
            $this->display('/Common/footer');
        }
    }

    /**
     * 增加地址
     * ajax
     */
	public function appendAddress(){
		if(IS_POST){
			//如果地址数小于10
			if($this->addressCount()<10){
				$data=array();
				$data['ad_address']=I('ad_address');
		    	$data['ad_consignee']=I('ad_consignee');
		    	$data['ad_phone']=I('ad_phone');
		    	$data['ad_default']=I('ad_default');
		    	$data['user_id']=getUser();	
		    	$Address = D('Address');
		    	//如果要默认地址数大于零 
		    	// var_dump($this->defAddressCount());	
		    	if(I('ad_default')=='1'){
			    	if($this->defAddressCount()!=='0'){
						//先删除默认标识再增加地址
						if($this->cancelDefAddress()){
							if($data['ad_address']!==''&&$data['ad_consignee']!==''&&$data['ad_phone']!==''){
				    			print_r($Address->add($data)?true:false);
							}
						}
					}else{
				    	if($data['ad_address']!==''&&$data['ad_consignee']!==''&&$data['ad_phone']!==''){
				    		print_r($Address->add($data)?true:false);
						}
					} 
		    	}else{
			    	if($data['ad_address']!==''&&$data['ad_consignee']!==''&&$data['ad_phone']!==''){
			    		print_r($Address->add($data)?true:false);
					}
				} 
	        }
		}
	}


    /**
     * 删除地址
     * ajax
     */
    public function deleteAddress(){
    	if(IS_POST){
    		$address_id=I('address_id');
	    	$Address = D('Address');
	    	if($Address->where("address_id='$address_id'")->delete()){
	    		//默认地址数
	    		// var_dump($this->addressCount());
				if($this->addressCount()=='0'){
					echo 1;
				}else{
					if ($this->defAddressCount()=='0') {
						echo $this->setLastAddressDef()?1:0;
					}else{
						echo 1;
					}
				}
	    	}
    	}
    }
    
    /**
     * 更新地址
     * ajax
     */
    public function updateAddress(){
    	if(IS_POST){
    		$data=array();
			$data['ad_address']=I('ad_address');
	    	$data['ad_consignee']=I('ad_consignee');
	    	$data['ad_phone']=I('ad_phone');
	    	$address_id=I('address_id');
	    	$Address = D('Address');
	    	print_r($Address->where("address_id='$address_id'")->save($data));
    	}
    }


   /**
 	*设置默认地址
 	*ajax
    */
    public function setDefAddress(){
		$Address = D('Address');
		$address_id=I('address_id');
    	if(IS_POST){
    		//如果找不到默认地址
    		if($this->getDefAddress()==false){
				$data=array();
	    		$data['ad_default']=1;
		    	print_r($Address->where("address_id='$address_id'")->save($data));
			}else{
			//如果找到,先取消标识,再设定
	    		if($this->cancelDefAddress()){
		    		$data=array();
		    		$data['ad_default']=1;
			    	print_r($Address->where("address_id='$address_id'")->save($data));
	    		}
			}
    	} 	
    }

    /**
	 * 查找该用户所有地址
	 */
	public function getAddress(){
		$Address = D('Address');
    	$user_id=getUser();	
    	return $Address->where("user_id='$user_id'")->select();    
	}

	/**
	 * 用户地址数
	 */
	public function addressCount(){
		$Address = D('Address');
    	$user_id=getUser();	
    	return $Address->where("user_id='$user_id'")->count();
	}

    /**
    *默认地址数
    */
    public function  defAddressCount(){
    	$Address = D('Address');
    	$user_id=getUser();
    	return $Address->where("user_id='$user_id' AND ad_default=1")->count();
    }

    /**
     * 查找默认地址
     */
    public function getDefAddress(){
    	$Address = D('Address');
    	$user_id=getUser();
    	return $Address->where("user_id='$user_id' AND ad_default=1")->find();
    }

  	/**
  	 *取消默认地址的默认标识
  	 */
    public function cancelDefAddress(){
    	$cancel=$this->getDefAddress();
    	$address_id = $cancel['address_id'];
    	$data=array();
    	$data['ad_default']='';
    	$Address = D('Address');
	    return $Address->where("address_id='$address_id'")->save($data);
    }
    /*
     * 设定最后一条地址为默认地址 
     */
    public function setLastAddressDef(){
    	$d=$this->getAddress();
    	$a=end($d);
    	$address_id=$a['address_id'];
    	$data=array();
    	$data['ad_default'] = '1';
    	$Address=D('Address');
    	return $Address->where("address_id='$address_id'")->save($data);
    }


    /**
     * 查找最新增加的三个地址
     */
    public function getThreeAddress(){
    	$Address = D('Address');
    	$user_id=getUser();
    	return $Address->where("user_id='$user_id'")->order("address_id desc")->limit(3)->select();
    }
   

}