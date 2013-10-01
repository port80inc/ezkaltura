<?php
require_once( 'kernel/common/template.php' );
require_once('extension/ezkaltura/classes/ezkaltura_config.php');
require_once("extension/ezkaltura/lib/KalturaClient.php");

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

//-- ▼初期値セット▼ --
// 表示形式
$view_mode = 2;
if (array_key_exists('view_mode', $Params) && in_array($Params['view_mode'], array(1, 2))) {
    $view_mode = $Params['view_mode'];
}

// ページ番号
$page = 1;
if (array_key_exists('page', $Params)) {
    // ページ番号を確認し、あれば上書き、なければそのままのデータを代入
    $page = intval($Params['page']) ? intval($Params['page']) : $page;
}

// 表示データ数
$page_size = 10;
if (array_key_exists('page_size', $Params) && in_array($Params['page_size'], array(10, 25, 50))) {
    // ページ番号を確認し、あれば上書き、なければそのままのデータを代入
    $page_size = intval($Params['page_size']);
}
//-- ▲初期値セット▲ --

$tpl = templateInit();
$Result = array();
$page = 1;
if (array_key_exists('page', $Params)) {
    // ページ番号を確認し、あれば上書き、なければそのままのデータを代入
    $page = intval($Params['page']) ? intval($Params['page']) : $page;
}


$kaltura_partner_object = unserialize($data['serialize_user']);

$ez_kaltura_comm = new eZKalturaComm();
$ez_kaltura_comm->setKalturaSession($data['partner_id'], $data['server_url'], $data['admin_secret']);

$pager = new KalturaFilterPager();
$pager->pageSize = $page_size;
$pager->pageIndex = $page;
$filter = new KalturaMixEntryFilter();
$filter->orderBy = "-createdAt";
try 
{
    $response = $ez_kaltura_comm->client->mixing->listAction($filter, $pager);
   if(count($response->objects) == 0 && $page > 1) {
        // データの有無をチェックし、全くなければ、1ページ目へ強制リダイレクト
        $url = $Module->redirectionURI( 'kaltura', 'gallery_media', array(1, $page_size, $view_mode));
        return $Module->redirectTo($url);
    }
}
catch (Exception $ex)
{
    $error = $ex->getMessage(); 
}
$count = $response->totalCount;

$ezkaltura_data = array();
if ($count > 0) {
    $entry_ids = array();
    // entry_idを取り出す
    if(count($response->objects) > 0) {
        foreach ($response->objects as $object) {
            array_push($entry_ids, $object->id);
        }
        // 配列を作り直す
        if($temp_ezkaltura_data = eZKaltura::fetchDataByEntryIds($entry_ids)){
            $temp_ezkaltura_data = getMaxVersion($temp_ezkaltura_data);
            foreach($temp_ezkaltura_data as $value) {
                // contentobject_attributeをfetch
                // 属性データを取得する
                $contentobject_attribute = eZContentObjectAttribute::fetch( $value['contentobject_attribute_id'], $value['version'], true );
                $contentobject = eZContentObject::fetch( $contentobject_attribute->ContentObjectID );
                $node = eZContentObjectTreeNode::fetchByContentObjectID( $contentobject_attribute->ContentObjectID );
                $node = $node[0];
                // クラスを取得する
                $content_class = $contentobject->contentClass();
                // オブジェクトから、生成するidentifierを元に名称を取得
                $id = $value['entry_id'];
                $value['version'] = $value['version'];
                $value['contentobject_attribute_id'] = $contentobject_attribute->ID;
                $value['class_identifier'] = $contentobject->ClassIdentifier;
                $value['url_alias'] = $node->UrlAlias();
                $value['object_name'] = $content_class->contentObjectName($contentobject);
                $ezkaltura_data[$id] = $value;
            }
        }
    }
}

foreach($response->objects as $key => $value) {
    // 配列にキャスト
    $value = $ez_kaltura_comm->setMyMetadata((array)$value);
    $entry_id = $value['id'];
    if (array_key_exists($entry_id, $ezkaltura_data)) {
        $value['ezkaltura'] = $ezkaltura_data[$entry_id];
    } else {
        $value['ezkaltura'] = false;
    }

    // フラッシュパラメータを生成
    $flash_vars = array();
    $flash_vars['partnerId'] = $kaltura_partner_object['id'];
    $flash_vars['subpId']    = ($kaltura_partner_object['id'] * 100);
    $flash_vars['uid']       = 'USERID';
    $flash_vars['ks'] 		  = $ez_kaltura_comm->ks;
    $flash_vars['kshowId']   = -1;
    $flash_vars['entryId']   = $value->id;
    $value['flash_vars']      = json_encode($flash_vars);
    $response->objects[$key] = $value;
}

// テンプレートへデータをアサイン
$tpl->setVariable('user'          , $data);
$tpl->setVariable('page'          , $page);
$tpl->setVariable('error'         , $error);
$tpl->setVariable('page_size'     , $page_size);
$tpl->setVariable('view_mode'     , $view_mode);
$tpl->setVariable('server_url'    , $ez_kaltura_comm->server_url);
$tpl->setVariable('response'      , $response->objects);
$tpl->setVariable('response_count', $response->totalCount);

// 出力テンプレート、タイトルを指定
$Result['left_menu'] = 'design:kaltura/left_menu.tpl';
$Result['content']   = $tpl->fetch( 'design:kaltura/gallery_mix.tpl');
$Result['path']      = array( array( 'url' => false,
                                        'text' => 'Kaltura GalleryMix' ) );
//}


?>