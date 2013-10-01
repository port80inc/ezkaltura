<?php
require_once( 'kernel/common/template.php' );
require_once("extension/ezkaltura/lib/KalturaClient.php");
require_once('extension/ezkaltura/classes/ezkaltura_config.php');

$http = eZHTTPTool::instance();

//-------------
// 前フィルター
//-------------
$data = eZKalturaConfig::getData();
if (!$data) {
    // kalturaの情報が登録されていないので、設定画面へリダイレクト
    $url = $Module->redirectionURI( 'kaltura', 'config');
    return $Module->redirectTo($url);
}

//-------------
//   通常処理
//-------------
$tpl = templateInit();
$Result = array();

// アカウント情報
$kaltura_partner_object = unserialize($data['serialize_user']);

$ez_kaltura_comm = new eZKalturaComm();
$ez_kaltura_comm->setKalturaSession($data['partner_id'], $data['server_url'], $data['admin_secret']);

$flash_vars = array();
$flash_vars["partnerId"]       = $kaltura_partner_object['id'];
$flash_vars["subpId"]          = $kaltura_partner_object['id'] * 100;
$flash_vars["uid"]             = "USERID";
$flash_vars["sessionId"] 	     = $ez_kaltura_comm->ks;
$flash_vars["kshowId"] 		 = -1;
$flash_vars["showCloseButton"] = false;

// テンプレートへデータをアサイン
$tpl->setVariable('user'      , $data);
$tpl->setVariable('server_url', $ez_kaltura_comm->server_url);
$tpl->setVariable('flash_vars', json_encode($flash_vars));


// 出力テンプレート、タイトルを指定
$Result['left_menu'] = 'design:kaltura/left_menu.tpl';
$Result['content']   = $tpl->fetch( 'design:kaltura/add_media.tpl');
$Result['path']      = array( array( 'url' => false,
                                        'text' => 'Kaltura AddMedia' ) );

?>