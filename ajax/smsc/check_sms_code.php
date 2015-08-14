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

$response=array(
    'message' => 'Not modified',
    'code' => 304
);
$rest = new RESTful('send_code',array('phone','code'));

try {
    print_r($rest->data);
    $user_id=$modx->user->id;
    $ask_code=$rest->data['code'];

    // Подключение к БД
    $db = new Database($pdoconfig);
    $state=$db->getOneWhere('modx_sms_validator', "status='sent' AND user_id='$user_id' AND phone='".$rest->data['phone']."'", 'id,code_sent,phone,attempts');
    if(empty($state)) throw new Exception('No such phone entry', 404);
    $stored_codes=$state['code_sent'];

    if(in_array($ask_code,explode(',',$stored_codes))){
        $state['status']='checked';
        $state['code_sent']='';
        $response['message']='Done';
        $response['code']=200;
    }else{
        $state['attempts']=$state['attempts']+1;
        $response['message']='Wrong code';
        $response['code']=403;
        $response['attempts']=$state['attempts'];
    }
    if($state['attempts']>10) $state['status']='blocked';

// Сохраняем состояние в БД
    $res=$db->updateOne('modx_sms_validator', $state['id'], $state);
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