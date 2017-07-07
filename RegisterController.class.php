<?php
namespace Home\Controller;
use Think\Controller;
class RegisterController extends Controller {

	public function register(){
        $User= D('User');
        if(!IS_POST){
            $this->display('/Common/header');
            $this->display('/Common/search_nav');
        	$this->display();
        	$this->display('/Common/footer');
        }else{
            //如果有post数据
            //注意!! 调用函数会直接输出函数计算值在页面
           if($this->chUser()=='0'&&$this->chPassword()=='0'&&$this->chRepassword()=='0'&&$this->chPhone()=='0'){
                echo $this->chUser();
                //将提交信息写入数据库
                $salt=$this->salt();
                $_POST['salt']=$salt;
                $_POST['password']=md5($_POST['password'].$salt);
               echo  $User->add($_POST)?'注册成功':'注册失败';
               //$this->assign('tips',$tips);
                $this->display('/Common/header');
                $this->display('/Common/search_nav');
        		$this->display();
        		$this->display('/Common/footer');
           }else{     
                $this->display('/Common/header');
                $this->display('/Common/search_nav');
        		$this->display();
        		$this->display('/Common/footer');
           }
        }
    }
    public function salt(){
        $salt='qwertyuiopasdfghjkl1234098765zxcvbnm';
        return substr(str_shuffle($salt),0,10);
    }
    //此函数输出给ajax  往下类推
    public function ajaxUser(){
        print_r($this->chUser());  
    }
    //此函数返回给register函数 往下类推
    public function chUser(){
            $rules = array(
          //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),          
            array('user','','1','3','unique','1')  
        );
        if(IS_POST){
             $User = M("User"); // 实例化User对象
            if (!$User->validate($rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
           return $User->getError();
            }else{
            // 验证通过 可以进行其他数据操作
            return  '0';
            }
        }
    }

    public function ajaxPassword(){
        print_r($this->chPassword());  
    }
    public function chPassword(){
            $rules = array(
          //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),          
            //密码长度5-20之间
            array('password','5,20','1','1','length','1')
             //array('password','\\','密码必须是英文数字','1','regex','1'),      
        );
        if(IS_POST){
             $User = M("User"); // 实例化User对象
            if (!$User->validate($rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            
            return $User->getError();
            }else{
            // 验证通过 可以进行其他数据操作
            return '0';
             //echo  '0';
             // echo  $User->add($_POST)?"11":"00";
            }
        }
    }

    public function ajaxRepassword(){
        print_r($this->chRepassword());  
    }
    public function chRepassword(){
            $rules = array(
          //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),          
            // 验证确认密码是否和密码一致
            array('repassword','password','1',0,'confirm'),     
        );
        if(IS_POST){
             $User = M("User"); // 实例化User对象
            if (!$User->validate($rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            return $User->getError();
            }else{
            // 验证通过 可以进行其他数据操作
            return '0';   
            }
        }
    }

    public function ajaxPhone(){
        print_r($this->chPhone());  
    }
    public function chPhone(){
            $rules = array(
          //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),          
            //手机长度不符合
            array('phone','11','1','1','length','1'),
            //该手机已注册
            array('phone','','2','1','unique','1')        
        );
        if(IS_POST){
             $User = M("User"); // 实例化User对象
            if (!$User->validate($rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            return $User->getError();
            }else{
            // 验证通过 可以进行其他数据操作
            return  '0';
            
            }
        }
    }
    
  


}