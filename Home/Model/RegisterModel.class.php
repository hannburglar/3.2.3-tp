<?php  
namespace Home\Model;
 use Think\Model;

 class RegisterModel extends Model{
 	
 	public function ru(){
		echo '<br>我是reg<br>'; 		
 		 $l = D('User');
 		 $l -> t();
 		 echo '<br>我是reg';
 	}





 }