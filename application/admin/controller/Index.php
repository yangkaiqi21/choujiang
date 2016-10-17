<?php

namespace app\admin\controller;

use think\Controller;
use think\Session;

class Index extends Controller
{

   public function login(){
       if(!empty($_POST)){
           if (input('post.username') == 'admin' && input('post.password') == 'aaa') {
               Session::set('admin_id',1);
               $this->success('登录成功', 'index/prize');
           } else {
               return '用户名密码错误';
           }
       }
       return $this->fetch();
   }

    public function prize(){
        if(Session::get('admin_id') != 1){
            $this->error('未登录', 'index/login');
        }
        $where = array();
        if(!empty($_POST['username'])){
            $where['user'] = $_POST['username'];
        }
        if(!empty($_POST['mobile'])){
            $where['mobile'] = $_POST['mobile'];
        }
        $list = db('prize_list')->whereor($where)->paginate();
        $this->assign('list',$list);
        $prizeInfo = db('lottery')->select();
        $this->assign('info',$prizeInfo);
        return $this->fetch('prize');
    }

    public function update($id){
        if(Session::get('admin_id') != 1){
            $this->error('未登录', 'index/login');
        }
        $update = db('prize_list')->where(['id'=>$id])->update(['isSend'=>1]);
        if($update){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }


}