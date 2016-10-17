<?php
namespace app\index\controller;

use think\Model;

class Lottery {
    protected $awardsArr= array(
        '0' => array('id'=>1,'name'=>'平板电脑','probability'=>1),
        '1' => array('id'=>2,'name'=>'数码相机','probability'=>5),
        '2' => array('id'=>3,'name'=>'音箱设备','probability'=>10),
        '3' => array('id'=>4,'name'=>'4G优盘','probability'=>12),
        '4' => array('id'=>5,'name'=>'10Q币','probability'=>22),
    );
    protected $proField;
    protected $proSum = 0;
    protected $checkAward = false;
    protected $table = false;
    const SUCCESS_CODE = 0;
    const FAIL_CODE = -1;

    /**
     * Lottery constructor.
     *
     * @param        $param         奖品设置数组或者奖品数据库表名
     * @param string $probability   概率字段, 默认: probability
     * @param string $noMsg         没有中奖的文字说明
     */
    public function __construct($param, $probability='probability', $noMsg='明天再来没准就能中大奖哦!'){
        if(is_array($param)){
            $this->awardsArr = $param;
        }elseif(is_string($param)){
            $this->table = $param;
            $this->awardsArr = db($param)->where('status = 0')->select();
        }else{
            $this->failRoll('奖项数据不正确!');
        }
        $this->proField = $probability;
        $percentage = 0;
        foreach ($this->awardsArr as $key => $val) {
            $arr[$val['id']] = ($val['total'] == 0)?0:$val[$this->proField];
            $this->awardsArr[$key]['key'] = 'yes';
            $percentage = $percentage + $val[$this->proField];
            $id = $val['id'] + 1;
        }
        array_push($this->awardsArr, array('id' => $id, 'name' => $noMsg, 'key' => 'no', $this->proField => (100 - $percentage)));
        $this->checkAwards();
    }

    /**
     * 检查抽奖数据
     * @return bool
     */
    protected function checkAwards(){
        if(!is_array($this->awardsArr) || empty($this->awardsArr)){
            return $this->checkAward = false;
        }
        $this->proSum = 0;
        foreach ($this->awardsArr as $_key => $award){
            $this->proSum += $award[$this->proField];
        }
        if(empty($this->proSum)){
            return $this->checkAward = false;
        }
        return $this->checkAward = true;
    }

    protected function successRoll($rollKey){
        if($this->table){
            if($this->awardsArr[$rollKey]['key'] == 'yes'){
                db($this->table)->where('id = '.$this->awardsArr[$rollKey]['id'])->setDec('total');
            }
        }
        $prize = $this->awardsArr[$rollKey];
        unset($this->awardsArr[$rollKey]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($this->awardsArr); //打乱数组顺序
        for($i=0;$i<count($this->awardsArr);$i++){
            $pr[] = $this->awardsArr[$i];
        }
        return array('errcode' => self::SUCCESS_CODE, 'roll_key' => $rollKey, 'msg' => '抽奖操作成功!', 'prize' => $prize, 'awards' => $pr);
    }

    protected function failRoll($msg = 'roll fail'){
        return array('errcode' => self::FAIL_CODE, 'msg' => $msg );
    }

    /**
     * 经典概率算法
     * @param $proArr
     *
     * @return int|string
     */
    private function getrand($proArr) {
        $result = '';
        $proSum = array_sum($proArr);       //概率数组的总概率精度
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    /**
     * 抽奖
     * @param   string  $type   算法默认是基础算法, base-->基础算法, rand-->经典算法
     * @return  array
     */
    public function roll($type='base') {
        if (false == $this->checkAward) {
            return $this->failRoll('奖品数据格式不正确!');
        }
        switch($type){
            case 'base':
                $result = mt_rand(0, $this->proSum);
                $proValue = 0;
                foreach ($this->awardsArr as $_key => $value) {
                    $proValue += $value[$this->proField];
                    if ($result <= $proValue) {
                        return $this->successRoll($_key);
                    }
                }
            case 'rand':
                foreach ($this->awardsArr as $key => $val) {
                    $arr[$val['id']] = $val[$this->proField];
                }
                return $this->successRoll(($this->getrand($arr)-1));
        }
        return $this->failRoll('抽奖失败啦!');
    }
}