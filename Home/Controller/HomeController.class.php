<?php
namespace Home\Controller;
use Think\Controller;
class HomeController extends Controller {
    
    public function home(){
        $this->display('/Common/header');
        $this->display('/Common/search_nav');
        $this->display();
        $this->display('/Common/footer');
    } 


    /*
    树状图
     */
    public function tree($parent_id=0,$lv=0){
        $Cat=D('Cat');
        static $cd=array();
        static $aa=array();
        foreach ($Cat->select() as $k => $v) {
            if($v['parent_id']==$parent_id){ 
                //增加数组单位i来区分等级
                $v['lv']=$lv;
                //找到的类目放进静态数组里面累积
                $cd[]=$v;
                $max[]=$lv;
            //调用自己进行下一步筛选    
            $this->tree($v['cat_id'],$lv+1);
            }

        }
        return array($cd,$max);
    }
 
    public function leftnav(){
        $rtree=($this->tree());
        $tree=$rtree[0];
        //print_r($tree);
        $maxlv=max($tree[1]);
        static $a=array();
        static $b=array();
        foreach ($tree as $k => $v) {
            if($v['lv']==0){
               $a[]=$v;
            }
            if($v['lv']==1){
                $b[]=$v;
            }
        }  
        for ($j=0; $j <count($a) ; $j++) { 
            $c=array();
            for ($i=0; $i <count($b) ; $i++) { 
                if($b[$i]['parent_id']==$a[$j]['cat_id']){    
                    $c[]=$b[$i];   
                }
            }
            $a[$j]['child']=$c;
        }
        return($a);
    }

    public function breadcrumb($cat_id){
        //通过产品id找到产品的栏目id ,通过栏目id找到栏目的父栏目id,一直找到最顶端父栏目id
        $Cat=D('Cat');
        $a=array();
        $b=array();
        //$cat=$Cat->where("cat_id=$cat_id")->find();
        //通过循环找到栏目的父栏目知道最顶端
        while($cat_id>0){
            $cat=$Cat->where("cat_id=$cat_id")->find();
            $a[]=$cat['cat_name'];
            $b[]=$cat['cat_id'];
            $cat_id=$cat['parent_id'];
        }
        array_reverse($a);
        array_reverse($b);
        return array($a,$b);

    }

   /**
    * 检测有没有登陆返回给js
    * header.js使用
    */
    function checkSession(){
        print_r(checkSession());
    }









   

}