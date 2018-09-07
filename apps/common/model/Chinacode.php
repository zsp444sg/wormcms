<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class Chinacode extends Model{

    protected function codelist(){
        if(webCacheHas('chinacode')){
            $codelist = webCacheGet('chinacode');
        }else{
            $data = $this->order('zoneid asc')->select();
            foreach ($data as $key => $val){
                if($val['zonelevel'] == '1'){
                    $sheng[] = $val->toArray();
                }
            }
            foreach ($data as $key => $val){
                if($val['zonelevel'] == '2'){
                    $shi[] = $val->toArray();
                }
            }
            foreach ($data as $key => $val){
                if($val['zonelevel'] == '3'){
                    $qu[] = $val->toArray();
                }
            }
            $codelist['sheng'] = $sheng;
            $codelist['shi'] = $shi;
            $codelist['qu'] = $qu;
            webCacheSet('chinacode',$codelist);
        }
        return $codelist;
    }
    public function allcode(){
        $allcode = $this->codelist();
        return $allcode;
    }
    public function chalist($data){
        $allcode = $this->codelist();
        switch ($data['level']){
            case '1':
                $chalist = $allcode['sheng'];
                break;
            case '2':
                foreach ($allcode['shi'] as $key => $val){
                    if($val['parzoneid'] == $data['parzoneid']){
                        $chalist[] = $val;
                    }
                }
                break;
            case '3':
                foreach ($allcode['qu'] as $key => $val){
                    if($val['parzoneid'] == $data['parzoneid']){
                        $chalist[] = $val;
                    }
                }
                break;
        }

        return $chalist;
    }

}