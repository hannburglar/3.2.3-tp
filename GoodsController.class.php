<?php
namespace Home\Controller;
use Think\Controller;
class GoodsController extends Controller {

    public function goods(){
        //左侧分类栏
        $tree=($this->leftnav());
        $this->assign('tree',$tree);
        // print_r($tree);
        static $a;
        foreach ($tree as $k => $v) {
             $a.= '<li class="lli"><a href="'.U("Home/Goods/goods").'?cat_id='.$v["cat_id"].'">'.$v["cat_name"].'</a><ul class="level1">';
            foreach($v['child'] as $ke => $va){
                $a.=  '<li class="lli"><a href="'.U("Home/Goods/goods").'?cat_id='.$va["cat_id"].'">'.$va["cat_name"].'</a></li>';
               
            }
            $a.= '</ul></li>';
        }
        $this->assign('a',$a);
        $cat_id= I('cat_id')?I('cat_id'):'0';
        $page =I('page')?I('page'):'1';
        //分页和商品数据
        $dap = $this->outputDataAndPage($cat_id,$page);
        print_r($dap[1]);
        $this->assign('goods',$dap[0]);     //模板赋值商品数据
        $this->assign('page',$dap[1]);       //模板赋值分页
        //面包屑导航
        $bread = $this->outputBreadcrumb($cat_id);
        $this->assign('bread',$bread);
        $this->display('/Common/header');
        $this->display('/Common/search_nav');
        $this->display();
        $this->display('/Common/footer');
    }

    /*______________________________________________
    递归面包屑导航
    返回栏目数组
     */
    public function breadcrumb($cat_id){
        $C=M('Cat');
        $cat = $C->select();
        $count = count($cat);
        static $data = array();
        for ($i=0; $i <$count ; $i++) {     //找到这个cat_id的数据
            if($cat[$i]['cat_id'] == $cat_id){
                $thisCat = $cat[$i]; 
                break;
            }
        }
        $data[] = $thisCat;     //将本栏目放入数组
        if($thisCat['parent_id'] !== '0'){      //如果本栏目父栏目id不是0
             // $data[] = $thisCat;     //将本栏目信息放入数组
            for ($j=0; $j <$count ; $j++) {  //找出此栏目的父栏目id
                if($cat[$j]['cat_id'] == $thisCat['parent_id']){
                    $cat_id = $cat[$j]['cat_id'];
                }
            }
            return $this->breadcrumb($cat_id);
        }
            return (array_reverse($data));      //翻转数组并返回
    }
    /*
    根据id输出面包屑导航
     */
    function outputBreadcrumb($cat_id){
        if($cat_id!=='0'){      //如果栏目id不等于0 就输出面包屑
            return $this->breadcrumb($cat_id);
        }
    }
   

    /*______________________________________________*/
    /*
    输出分页和数据
     */
    public function outputDataAndPage($cat_id,$page){
            $cat_idd[] = $cat_id;   //变成数组形式
            $idArr = ($this->childrenCat($cat_idd));    //找出该栏目id以及子栏目id
            $catStr = 'cat_id in('.implode(',', $idArr).')'; //拆分数组组合成语句
            return  $this->dataAndPage($page,$catStr,$cat_id);   //分页和数据

        }
    /*
    递归向下找该栏目的子栏目
    $cat_id 是一维数组
    返回该栏目和子栏目的一个数组
     */
    public function childrenCat($cat_id=[1]){
        $C=M('Cat');
        $cat = $C->select();
        static $data = array();
        for ($i=0; $i <count($cat_id) ; $i++) {             
            $data[] = $cat_id[$i];
            for ($j=0; $j <count($cat) ; $j++) { 
               if($cat_id[$i] == $cat[$j]['parent_id']){
                    $shouji[] = $cat[$j]['cat_id'];
                    $data[] = $cat[$j]['cat_id'];
               } 
            }
        }     
        if(count($shouji)!==0){     //如果收集数组没有值就结束递归
            $cat_id = $shouji;
            return $this->childrenCat($cat_id);
        } 
        $da = array_unique($data); //移除重复值
        $res = array_merge($da);      //重排索引
        return $res;
    }
    
    /*
    数据和分页
     */
    public  function dataAndPage($wPage,$catStr,$cat_id){
        // echo  $cat_id.'<br><br>';
        $G=M('Goods');
        $data = $G->field("goods_name,shop_price,goods_img,thumb_img")->where($catStr)->order('goods_id')->limit($wStartG,$mQuantity)->select();
        $mQuantity = 8;     //每页有多少个商品
        $dCount = intval($G->count());      //总共有多少个商品
        // echo '当前第'.$wPage.'页<br>';   
        $wStartG = $mQuantity*($wPage-1);   //从第几个商品开始取
        $pageQ = ceil($dCount/$mQuantity);      //总共多少页
        // echo '总共有'.$pageQ.'页<br>';
        $pa = $this->page($wPage,$pageQ);
        foreach($pa as $k => $v){
            $page[]='<a href="?cat_id='.$cat_id.'&page='.$v[0].'">'.$v[1].'</a>';
        }
        return array($data,$page);
        // return $data;
    }
    /*
    原生分页数组
    $wPage = 1;     //当前第几页
    $pageQ = ceil($dCount/$mQuantity);      //总共多少页
     */
    public function page($wPage,$pageQ){
        $pageC=5;       //要显示多少页
        $pageZ = ceil($pageC/2);//显示多少页的中位数
        // echo '中位数='.$pageZ.'<br>';
        $pageArr = array();     //分页数组 
        if($wPage <= $pageZ){   //当前页数小于显示多少页的中位数
            // echo '小<br>';
            $behind = $pageQ-$wPage;
            // echo '还有'.$behind.'到尾<br>';
            $j = 1;
            $count = $pageC;
            for ($i=0; $i < $count; $i++) {      //循环输出分页
                 $pageArr[] = [$j,$j];
                // $pageArr[$j] = '<a href="?page='.$j.'">'.$j.'</a>';
                $j++;
            }
            $jumpF = [$wPage+$count,'|>>'];
            $pageArr[] = $jumpF;       //加入跳前按钮
        }else{
            // echo '大<br>';
            $front = floor($pageC/2); //显示该页前面有多少页
            $behind = $pageQ-$wPage;    //后面还有多少页显示
            // echo '还有'.$behind.'到尾<br>';
            $count = $pageC;    
            if($behind<$front){     //后面页数不够时候
                $count = $front+1+$behind;
            }
            $j = $wPage-$front;   
            for ($i=0; $i < $count; $i++) {     //循环输出分页
                $pageArr[] = [$j,$j];
                // $pageArr[$j] = '<a href="?page='.$j.'">'.$j.'</a>';
                $j++;
            }
            //添加跳回按钮
            if($wPage>$pageZ){      //当前页数大于中位数
                $jumpB = [1,'<<|'];
                if($wPage>$pageC){      //当前页数大于要显示页数
                    $jumpB = [$wPage-$pageC,'<<|'];
                }
                array_unshift($pageArr,$jumpB);
            }
            //添加跳前按钮
            // $jumpF = '<a href="?page='.($wPage+$count).'">|>></a>';
              $jumpF = [$wPage+$count,'|>>'];
            if(($pageQ-$wPage)<$pageC){
                // $jumpF = '<a href="?page='.($pageQ).'">|></a>';
              $jumpF = [$pageQ,'|>>'];
            }
            if($behind>$front){     //后面页数足够时候        
                $pageArr[] = $jumpF;       //加入跳前按钮
            }
            
        }      
        return $pageArr;
    } 
    


    


    /*
    树状图
     */
    public function tree($parent_id=0,$lv=0){
        $Cat=M('Cat');
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
    
    /*
    左侧导航栏 
     */
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




    // $Goods=M('Goods');
        // //第几页
        // // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        // $goods = $Goods->order('goods_id')->page($p.',8')->select();
        //  //如果有cat_id
        // if(I('cat_id')){  
        //     $cat_id = I('cat_id');
        //     //根据栏目ID查询该栏目下的产品
        //     $goods = $Goods->where("cat_id=$cat_id")->order('goods_id')->page($p.',8')->select();  
        //     $thisCount= $Goods->where("cat_id=$cat_id")->count(); 
        //     // 该页的产品数据集
        //     $this->assign('goods',$goods);
        //    // 查询满足要求的总记录数     
        //     $count = $Goods->count();
        //     // 实例化分页类 传入总记录数和每页显示的记录数
        //     $Page = new \Think\Page($count,8);
        //     // 分页显示输出
        //     $show = $Page->show();
        //     //如果该栏目下产品数大于8,就输出分页,否则不输出   
        //     if($thisCount>8){     
        //     // 赋值分页输出
        //     $this->assign('page',$show);
        //     }
        //     //面包屑导航
        //     $bc=$this->breadcrumb($cat_id);
        //     //根据二维数组中下标分组
        //     for($j=0;$j<count($bc[0]);$j++){
        //         for($i=0;$i<2;$i++){
        //              $bread[$j][$i]=$bc[$i][$j];          
        //         }
        //     $this->assign('bread',array_reverse ($bread));
        //     }  
        // }else{
        //     $goods = $Goods->order('goods_id')->page($p.',8')->select(); 
        //     $this->assign('goods',$goods);// 该页的产品数据集
        //     $count = $Goods->count();// 查询满足要求的总记录数     
        //     $Page = new \Think\Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
        //     $show = $Page->show();// 分页显示输出
        //     //如果该栏目下产品数大于8,就输出分页,否则不输出   
        //     $this->assign('page',$show);// 赋值分页输出 
        // } 










}