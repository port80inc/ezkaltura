<?php
require_once( "kernel/common/template.php" );
require_once('extension/ezkaltura/classes/ezkaltura_comm.php');


// kalturaにアップロードされた動画のentryid
$entry_id = $_GET['entry_id'];

// バージョン
$version = intval($_GET['version']);

// 紐づけを行うcontentobject_attribute_id
$contentobject_attribute_id = intval($_GET['contentobject_attribute_id']);


$media_type = $_GET['media_type'];

// 指定のメディア以外は削除は行わない
if(!$media_type || !in_array($media_type, array('media', 'mixing'))) {
    return $Module->redirectTo($_SERVER['HTTP_REFERER']);
}
    
$data = eZKalturaConfig::getData();
if (!$data) {
    // データがない
    return false;
}

// インスタンス作成
$ez_kaltura_comm = new eZKalturaComm();
$ez_kaltura_comm->setKalturaSession($data['partner_id'], $data['server_url'], $data['admin_secret']);

// 削除処理を実行(他バージョンを含めたデータを削除する)
$ez_kaltura_comm->doDelete_DB_KalturaServer_With_OtherVersion($contentobject_attribute_id, $version, $entry_id, $media_type);

return $Module->redirectTo($_SERVER['HTTP_REFERER']);

?>
