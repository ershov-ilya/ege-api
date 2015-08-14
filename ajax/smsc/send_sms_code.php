<?php
/**
 * Created by PhpStorm.
 * Author:   ershov-ilya
 * GitHub:   https://github.com/ershov-ilya/
 * About me: http://about.me/ershov.ilya (EN)
 * Website:  http://ershov.pw/ (RU)
 * Date: 12.08.2015
 * Time: 17:18
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
defined('DEBUG') or define('DEBUG', false);
define('MAX_STORE_CODES',5);

header('Content-Type: text/plain; charset=utf-8');
require_once('../../core/config/core.private.config.php');

define('MODX_API_MODE',true);
require_once('../../../index.php');
/* @var modX $modx */
$site_name=$modx->getOption('site_name');

require_once(API_CORE_PATH.'/class/restful/restful.class.php');
require_once(API_CORE_PATH.'/class/format/format.class.php');
require_once(API_CORE_PATH.'/class/database/database.class.php');
require_once(API_CORE_PATH.'/config/pdo.private.config.php');
require_once(API_CORE_PATH.'/modules/smsc/send.func.php');
require_once(API_CORE_PATH.'/config/smsc.private.config.php');

$rest = new RESTful('send_code',array('phone'));
$response=array(
    'message' => 'Not modified',
    'code' => 304
);

try {
// Генерируем проверочный код
    $sms_code = rand(1000, 9999);

// Формируем сообщение
    $sms = array(
        'sender'    =>  'SYNERGY'
    );
    $sms['mes'] = $site_name.': Ваш проверочный код: ' . $sms_code;
    if (isset($rest->data['phone'])) $sms['phones'] = preg_replace('/[ \-_\(\)]/i', '', $rest->data['phone']);
    else throw new Exception('No phone field value', 400);

    print_r($sms);

// Подключение к БД
    $db = new Database($pdoconfig);

// Ищем телефон
    $found = $db->getOne('modx_sms_validator', $packet['phones'],'phone', 'id,user_id,status,phone,code_sent');
    if($found)
    {
        // Повторный запрос, модифицируем строку в БД
        $state=$found;
        $state['code_sent'] = $state['code_sent'].','.$sms_code;
        $check_arr=explode(',',$state['code_sent']);
        if(count($check_arr)>MAX_STORE_CODES) {unset($check_arr[0]); $state['code_sent']=implode(',',$check_arr);}
        $state['status']='ready';
        $db->updateOne('modx_sms_validator',$found['id'],$state);
    }else{
        // Номер не зарегистрирован, новая строка в БД
        $state=array(
            'phone' => $packet['phones'],
            'code_sent' => $sms_code
        );
        $state['user_id']=$modx->user->id;
        $state['id']=$db->putOne('modx_sms_validator',$state);
    }
    print_r($state);

// Отправляем смс
    $res=send_sms($sms, $smsc_config);
    print_r($res);

// Сохраняем состояние в БД
//    $res=$db->updateOne('modx_sms_validator', $state['id'], $state);
}
catch(Exception $e) {
    // При ошибке
    $response['response']=$e->getMessage();
    $response['code']=$e->getCode();
}

// Вывод ответа
require_once(API_CORE_PATH . '/class/format/format.class.php');
if(DEBUG) print Format::parse($response, 'plain');
else  print Format::parse($response, 'json');
// Формируем запись

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Конец скрипта
exit(0);

$data=$rest->data;



print_r($rest->data);
print_r($rest->scope);

$data=array(
    'mes' => 'API test',
    'phone' => '+79257123457'
);
print_r($data);
exit(0);

$res=send_sms($data, $smsc_config);
var_dump($res);