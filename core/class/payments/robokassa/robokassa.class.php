<?php
/**
 * Created by PhpStorm.
 * Author: ershov-ilya
 * Website: http://ershov.pw/
 * Date: 10.02.2015
 * Time: 12:51
 */

class Robokassa{
    private $baseURL;
    private $data;
    private $config;

    private $crc_sum1;
    private $payURL;

    private $crc_sum2;

    public function __construct($data, $config=array(), $testmode=false)
    {
        if(empty($config)) die('Lost Robokassa config!');
        $this->data = $data;
        $this->config=$config;
        if($testmode) $this->baseURL = 'http://test.robokassa.ru/Index.aspx?';
        else $this->baseURL = 'https://merchant.roboxchange.com/Index.aspx?';

    }

    private function genCRC1(){
        if(empty($this->crc_sum1)) {
            $pass1=$this->config['mrh_pass1'];
            $crc_data = array(
                'MrchLogin' => $this->config['MrchLogin'],
                'OutSum'    => $this->data['OutSum'],
                'InvId'     => $this->data['InvId']
            );
            $str=implode(':',$crc_data);
            $str= $str.':'.$pass1;
            $this->crc_sum1 = md5($str);
        }
        return $this->crc_sum1;
    }

    public function genCRC2(){
        if(empty($this->crc_sum2)) {
            $pass2 = $this->config['mrh_pass2'];
            $crc_data = array(
                'OutSum' => $this->data['OutSum'],
                'InvId' => $this->data['InvId']
            );
            if(empty($crc_data['InvId'])) return '';
            $str = implode(':', $crc_data);
            $str = $str . ':' . $pass2;
            $this->crc_sum2 = strtoupper(md5($str));
        }
        return $this->crc_sum2;
    }

    public static function checkCRC2($data, $config){
        $pass2 = $config['mrh_pass2'];
        $crc_data = array(
            'OutSum' => $data['OutSum'],
            'InvId' => $data['InvId']
        );
        $str = implode(':', $crc_data);
        $str = $str . ':' . $pass2;
        $crc_sum2 = strtoupper(md5($str));
        if($data['SignatureValue']==$crc_sum2) return true;
        return false;
    }

    function payURL(){
        if(empty($this->payURL)) {
            $this->genCRC1();
            $data = array('MrchLogin' => $this->config['MrchLogin']) + $this->data;
            $crc = $this->crc_sum1;
            $url = $this->baseURL;
            $result = '';

            foreach ($data as $key => $val) {
                $result .= $key . '=' . $val . '&';
            }
            $result .= 'SignatureValue=' . $crc;
            $this->payURL = $url.$result;
        }
        return $this->payURL;
    }

    public function resultArray(){
        $newdata = array(
            'crc2' => $this->genCRC2()
        );
        return $this->data + $newdata;
    }

}