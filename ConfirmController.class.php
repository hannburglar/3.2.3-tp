<?php
namespace Home\Controller;
use Think\Controller;
class ConfirmController extends Controller {
    	
	public function confirm(){
		if(checkSession()==false){	
			redirect("../Login/login", 0.1, ' ');
        }else{	
			$address=$this->assemblyAddress();
			//输出地址
			$this->assign('address',$address);
			//找默认地址
			foreach ($address as $k => $v) {
				if($v['ad_default']=='1'){
					$def=$v;
				}
			}

			$this->assign('def',$def);
			if (IS_POST) {
				$json = $_POST['shopcart'];
				$c = json_decode($json, true);
				$this->assign('json',$json);
				$s = A('Shopcart');
				$good=$s->getCheckedGoods($c);
				$this->assign('good',$good);	
			}
			$this->display('/Common/header');
	        $this->display();
	        $this->display('/Common/footer');
		}
	}

	/*
	组装地址数组
	 */
	public function assemblyAddress(){
		$Member=A('Member');
		$data=$Member->getThreeAddress();
		//默认地址
		$address=array();
		//最新加入的三个地址
		$address[]=$Member->getDefAddress();
		foreach ($data as $k => $v) {
			if($v['ad_default']!=='1'){
				$address[]=$v;
			}
		}
		return $address;
	}

	/*
	找需要编辑的地址
	 */
	public function pendingEditingAddress(){
		if(IS_POST){
			$address_id=I('address_id');			
			$address=$this->assemblyAddress();
			$editing=array();
			foreach ($address as $k => $v) {
				// print_r($v['address_id']);
				if($v['address_id']==$address_id){
					$editing[]=$v;
				}
			}
			echo json_encode($editing[0]);
			// print_r($editing[0]);
			
		}
	}

}