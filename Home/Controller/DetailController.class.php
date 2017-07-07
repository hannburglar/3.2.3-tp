<?php
namespace Home\Controller;
use Think\Controller;
class DetailController extends Controller {
    
   public function detail(){
        $Goods=D('Goods');
        $Cat=D('Cat');
        if(IS_GET){
            $goods_id = I('goods_id');
            $goods = $Goods->where("goods_id=$goods_id")->find();
            $this->assign('goods',$goods);
            $this->assign('desc',html_entity_decode($goods['goods_desc']));
            //面包屑导航
            $cat_id = $goods['cat_id'];
            $bc=$this->breadcrumb($cat_id);
            //根据二维数组中下标分组
            for($j=0;$j<count($bc[0]);$j++){
                for($i=0;$i<2;$i++){
                     $bread[$j][$i]=$bc[$i][$j];      
                }
            }
            $this->assign('bread',array_reverse ($bread));
        }   
        $this->display('/Common/header');
        $this->display('/Common/search_nav');
        $this->display();
        $this->display('/Common/footer');
    }
    
    /*
     *面包屑导航
     *通过产品id找到产品的栏目id ,通过栏目id找到栏目的父栏目id,一直找到最顶端父栏目id
     */
    public function breadcrumb($cat_id){
        $Cat=D('Cat');
        $a=array();
        $b=array();
        while($cat_id>0){   //通过循环找到栏目的父栏目知道最顶端
            $cat=$Cat->where("cat_id=$cat_id")->find();
            $a[]=$cat['cat_name'];
            $b[]=$cat['cat_id'];
            $cat_id=$cat['parent_id'];
        }
        array_reverse($a); //翻转数组
        array_reverse($b);
        return array($a,$b);

    }


    // /*
    // 获取购物车内容
    //  */
    // public function shopcartContent(){
    //     $user_id = getUser();
    //     $sCartC = 'sCart-C'.$user_id;
    //     $shop =  useCache($sCartC,$user_id,'file');
    //     $sCartS = 'sCart-S'.$user_id;
    //     $sig =  useCache($sCartS,$user_id,'file');
    //     if($shop == ''&& $sig !== 'noChange'){
    //         $S = A('shopcart'); 
    //         $shop = $S->getGoodsFromCart();
    //         useCache($sCartC,$user_id,'file',$shop,900);
    //         $sign = 'noChange';
    //         useCache($sCartS,$user_id,'file',$sign,900);
    //     }
    //     return $shop;
    // }
    // /*
    // 输出购物车内容的json数据
    //  */
    // public function reScartJsonToJs(){
    //     $sCart = $this->shopcartContent();
    //     print_r(json_encode($sCart));
    // }









}
