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
    ��״ͼ
     */
    public function tree($parent_id=0,$lv=0){
        $Cat=D('Cat');
        static $cd=array();
        static $aa=array();
        foreach ($Cat->select() as $k => $v) {
            if($v['parent_id']==$parent_id){ 
                //�������鵥λi�����ֵȼ�
                $v['lv']=$lv;
                //�ҵ�����Ŀ�Ž���̬���������ۻ�
                $cd[]=$v;
                $max[]=$lv;
            //�����Լ�������һ��ɸѡ    
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
        //ͨ����Ʒid�ҵ���Ʒ����Ŀid ,ͨ����Ŀid�ҵ���Ŀ�ĸ���Ŀid,һֱ�ҵ���˸���Ŀid
        $Cat=D('Cat');
        $a=array();
        $b=array();
        //$cat=$Cat->where("cat_id=$cat_id")->find();
        //ͨ��ѭ���ҵ���Ŀ�ĸ���Ŀ֪�����
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
    * �����û�е�½���ظ�js
    * header.jsʹ��
    */
    function checkSession(){
        print_r(checkSession());
    }









   

}