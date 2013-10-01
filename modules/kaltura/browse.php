<?php

require_once( "kernel/common/template.php" );
require_once('extension/ezkaltura/classes/ezkaltura_comm.php');

$tpl = templateInit();
$Result = array();


//kalturaサーバーとの連携
$contentobject_id = $Params['ContentObjectID'];
$contentobject_attribute_id = $Params['ContentObjectAttributeID'];
$version = $Params['Version'];
$mode = $Params['Mode'];

// インスタンス作成
$ez_kaltura_comm = new eZKalturaComm($contentobject_id, $contentobject_attribute_id);
if ($ez_kaltura_comm->client) {
    // 更新されている動画データがあれば更新を実行
    $ez_kaltura_comm->doUpdateKaltura($contentobject_attribute_id, $version);
    // 表示用のデータを取得
    $fet_obj_list = eZKaltura::fetchDataByContentObjectAttributeId($contentobject_attribute_id, $version);
    if (count($fet_obj_list) > 0) {
        $KalturaMediaType = new KalturaMediaType();
        foreach ($fet_obj_list as $key => $value) {
            if ($value['movie_id']) {
                // シリアライズ化されたデータを配列へ
                $fet_obj_list[$key]['serialized_metadata'] = $ez_kaltura_comm->setMyMetadata((array)unserialize($value['serialized_metadata']));
            } else {
                unset($fet_obj_list[$key]);
            }
        }
    }
} else {
    $error = "An error occurred.";
}

if ($error) {
	$tpl->setVariable('error', $error);
}

$tpl->setVariable('kaltura_client' , $ez_kaltura_comm);
$tpl->setVariable('mode' , $mode);
$tpl->setVariable('kaltura_data' , $fet_obj_list);
$Result['content'] = $tpl->fetch( 'design:kaltura/browse.tpl');
$Result['pagelayout'] = false;

?>
