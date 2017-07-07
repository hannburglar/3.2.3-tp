<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {

	public function login(){
       
        if(!IS_POST){
            $this->display('/Common/header');
            $this->display('/Common/search_nav');
        	$this->display();
        	$this->display('/Common/footer');
        }else{
            // 如果输入验证码正确
            $checkcode=I('post.checkcode');
            if($this->check_verify($checkcode)==false){

            }else{                  
                $referrer=(I('referrer'));
                $user=I('post.user');    
                $pwd=I('post.password');  
                $User= D('User');
                $userInfo=$User->where("user='$user'")->find(); 
                if(!$userInfo){
                     redirect('', 1, '用户名错误');
                }
                
                if($userInfo['password']!==md5($pwd.$userInfo["salt"])){
                     redirect('', 1, '密码错误');
                }else{                  
                    session_start();
                    $_SESSION['user']=$userInfo['user'];
                    $_SESSION['pwd']=$userInfo['password'];
                    $_SESSION['u_id']=$userInfo['user_id'];
                    redirect($referrer, 0.1,' ');
                }   
            }         
            $this->display('/Common/header');
            $this->display('/Common/search_nav');
        	$this->display();
        	$this->display('/Common/footer');
        }
       
    }    
    

    public function loginout(){
        session('user',null);
        session('pwd',null);
       redirect('login', 0.1, '退出成功');
    }

    //设置验证码
    public function verify(){
        $arr = array(
            'imageW'    =>    90,
            'imageH'    =>    40,
            'fontSize'    =>    13,    // 验证码字体大小
            'length'    =>    4,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
            'useCurve'    =>    true,
            'bg'        =>    array(238, 238, 238)
        );
        //清除缓冲区的内容保证验证码正常输出
        ob_clean();	
        $Verify = new \Think\Verify($arr);
        $Verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function check_verify($code, $id = ''){
        $config = array(
        'reset' => false // 验证成功后是否重置，这里才是有效的。
        );
        $verify = new \Think\Verify($config);
        return $verify->check($code, $id);
        }



    /**
     * 检测验证码
     * @return [type] [description]
     */
    public function chCode(){
        if(IS_POST){
           // var_dump($_POST);
            $checkcode=I('post.checkcode');
           // echo $checkcode;
            if($this->check_verify($checkcode)==false){
                echo '1';
            }else{
                //0为验证码正确
                echo '0';
             }
        }   
    }

    /**
    * 检测有没有登陆返回给js
    * header.js使用
    */
    function checkSession(){
        print_r(checkSession());
    }





}
