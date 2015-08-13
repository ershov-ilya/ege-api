<?php
/**
 * Created by PhpStorm.
 * Author:   ershov-ilya
 * GitHub:   https://github.com/ershov-ilya/
 * About me: http://about.me/ershov.ilya (EN)
 * Website:  http://ershov.pw/ (RU)
 * Date: 12.08.2015
 * Time: 17:50
 */

function send_sms($data, $smsc){
    $link='https://smsc.ru/sys/send.php';

    if(isset($data['phone']) && empty($data['phones'])) {
        $data['phones']=$data['phone'];
        unset($data['phone']);
    }
    $defdata=array(
        'sender'    =>  'SYNERGY',
        'phones'    =>  '',
        'mes'       =>  ''
    );
    $data=array_merge($defdata, $data, $smsc);
	
	print_r($data);
	exit(0);

    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
    #Устанавливаем необходимые опции для сеанса cURL
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'sbs.edu.ru-ershov.pw-bot');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    curl_setopt($curl,CURLOPT_HEADER,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
    curl_close($curl); #Завершаем сеанс cURL
    $response=array(
        'response'  => $out,
        'code'      => $code
    );
    return $response;
}