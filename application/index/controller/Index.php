<?php
namespace app\index\controller;

use think\Controller;
use app\index\Lottery;
use think\Cookie;
class Index extends Controller
{


    public function index()
    {
        $logic = new CryptDes();
        $logic->iv = $_GET['iv'];
        $logic->key = '5686457a711e0b9fda3d243796b70146';
        $info = $logic->decrypt($_GET['token']);
        $info = json_decode($info,true);
        $this->assign('info',$info);
        return $this->fetch();
    }

    public function roll(){
          if(empty($_POST['cardno'])){
              return array('code'=>0,'msg'=>'未登录');
          }
        $data = ['user'=>input('cardno'),'mobile'=>input('mobile')];
//        $cookie1 = Cookie::has('isDone');
//        $cookie2 = Cookie::has('zhongjiang');
//        if($cookie1){
//            return array('code'=>0,'msg'=>'今日已经抽过');
//        }else{
//            Cookie::set('isDone','1',86400);
//        }
//        if($cookie2){
//            return array('code'=>0,'msg'=>'明天再来没准就能中大奖哦!');
//        }else{
//            Cookie::set('zhongjiang','1',86400000);
//        }
//        $isDone = db('prize_list')->where(['user'=>$data['user'],'create_time'=>['like',date("Y-m-d").'%']])->select();
//        if(!empty($isDone)){
//            return array('code'=>0,'msg'=>'今日已经抽过');
//        }
//        $zhongjiang = db('prize_list')->where(['user'=>$data['user'],'status'=>1,'create_time'=>['like',date("Y-m-d").'%']])->select();
//        if(!empty($zhongjiang)){
//            return array('code'=>0,'msg'=>'明天再来没准就能中大奖哦!');
//        }
        $model = new \app\index\controller\Lottery('Lottery');
        $res = $model->roll();
        if($res['prize']['key'] == 'yes'){
            $data = [
                'name' => $res['prize']['name'],
                'user' => $data['user'],
                'create_time' => date('Y-m-d H:i:s'),
                'status' => 1,
                'mobile' => $data['mobile'],
            ];
            db('prize_list')->insert($data);
            return array('code'=>1,'status'=>1,'msg'=>'恭喜你获得'.$res['prize']['name'],'src'=>$res['prize']['imgpath']);
        }else{
            $data = [
                'name' => $res['prize']['name'],
                'user' => $data['user'],
                'create_time' => date('Y-m-d H:i:s'),
                'status' => 0,
                'mobile' => $data['mobile'],
            ];
            db('prize_list')->insert($data);
            return array('code'=>1,'status'=>0,'msg'=>$res['prize']['name']);
        }
    }


    public function seeSendMethod(){
        return $this->fetch();
    }
}
