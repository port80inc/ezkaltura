<?php
require_once( "kernel/common/template.php" );
require_once('extension/ezkaltura/classes/ezkaltura_comm.php');

// 紐づけを行うcontentobject_attribute_id
$contentobject_attribute_id = intval($_GET['contentobject_attribute_id']);
// バージョン
$version = intval($_GET['version']);
// kalturaにアップロードされた動画のentryid
$entry_ids = $_GET['entry_id'];

// contentobjectの公開バージョンを取得
$contentobject_id = $_GET['contentobject_id'];

// インスタンス作成
$ez_kaltura_comm = new eZKalturaComm($contentobject_id, $contentobject_attribute_id);

if ($ez_kaltura_comm->client) {
    $mode = $_GET['mode'];
    switch ($mode) {
        case 'insert':
            if (count($entry_ids) > 0) {
                try {
                    // entry_idに紐づくデータを取得し、配列へ
                    $data_kaltura = array();
                    foreach ($entry_ids as $key => $entry_id) {

                        // 複数対応にする場合は、以下に単一か複数か
                        // の条件を挿めば可能
                        if ($key == 0) {
                            // データ自体は単一登録のみ(最初のデータを登録の対象とする)
                            // kalturaオブジェクトを取得する
                            $data_kaltura[$entry_id] = $ez_kaltura_comm->client->media->get($entry_id);
                        } else {
                            // それ以外は、サーバーからデータを削除する
                            $ez_kaltura_comm->client->media->delete($entry_id);
                        }
                    }
                } catch (Exception $ex) {
                    $error = $ex->getMessage(); 
                }
                if (!$error) {
                    // kalturaサーバーから取得したデータはあるか？？
                    if (count($data_kaltura) > 0) {
                        $ezkaltura_id = $ez_kaltura_comm->doDelete_DB_KalturaServer($contentobject_attribute_id, $version);
                        // 登録分をDBに登録
                        foreach ($data_kaltura as $key_entry_id => $kaltura) {
                            // DBへデータを登録
                            $ez_kaltura_comm->doRegistKaltura($ezkaltura_id, $key_entry_id, $kaltura->dataUrl, $kaltura->downloadUrl,
                                                                    $kaltura->name, serialize($kaltura), $kaltura->width, $kaltura->height, $kaltura->thumbnailUrl);
                        }
                    }
                }
            }
        break;

        case 'delete':
            // 削除処理を実行
            $ez_kaltura_comm->doDelete_DB_KalturaServer($contentobject_attribute_id, $version);
        break;

        default:
        break;
    }

}

?>
