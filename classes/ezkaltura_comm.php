<?php
// DB操作ファイル
require_once( "lib/ezdb/classes/ezdb.php" );

// kaltura関連のファイルを読み込み
require_once("extension/ezkaltura/lib/KalturaClient.php");
require_once('extension/ezkaltura/classes/ezkaltura.php');
require_once('extension/ezkaltura/classes/ezkaltura_movie.php');
require_once('extension/ezkaltura/classes/ezkaltura_thumbnail.php');

class eZKalturaComm
{

    var $client        = null;
    var $partner_id   = null;
    var $server_url   = null;
    var $admin_secret = null;
    var $ks             = null;
    
    /**
     * APIに接続し、インスタンスを作成する
     * @param type $contentobject_id
     * @param type $contentobject_attribute_id 
     */
    function __construct($contentobject_id = null, $contentobject_attribute_id = null) {
        if ($contentobject_id && $contentobject_attribute_id) {
            $contentobject = eZContentObject::fetch( $contentobject_id );
            $current_version = $contentobject->attribute( 'current_version' );

            // 属性データを取得する
            $contentobject_attribute = eZContentObjectAttribute::fetch( $contentobject_attribute_id, $current_version, true );
            if ($contentobject_attribute) {
                // クラス属性情報を取得
                $classAttribute = $contentobject_attribute->contentClassAttribute();
                $this->setKalturaSession($classAttribute->attribute("data_int1"), $classAttribute->attribute("data_text1"), $classAttribute->attribute("data_text4"));
            }
        }
    }
    
    function setKalturaSession($partner_id, $server_url, $admin_secret) {
        // kalturaサーバーからデータを取得する
        $this->partner_id   = $partner_id;
        $this->server_url   = $server_url;
        $this->admin_secret = $admin_secret;
        if ($this->partner_id != '' && $this->server_url != '' && $this->admin_secret != '') {
            // kalturaサーバーへ接続
            $config = new KalturaConfiguration($this->partner_id);
            $config->serviceUrl = $this->server_url;
            $client = new KalturaClient($config);
            try {
                $ks = $client->session->start($this->admin_secret, "USERID", KalturaSessionType::ADMIN);
            } catch (Exception $ex) {
                $error = $ex->getMessage();
            }
            // エラー確認
            if (!$error) {
                // セッションの確立
                $client->setKs($ks);
                $this->ks = $ks;
                // API接続クラスを返す
                $this->client = $client;
            } 
        }
    }
    
    /**
     * データの削除
     * ※ ezpublishのバージョンと照らし合わせて、他バージョンでも使用していないようであれば、
     *    kalturaサーバーからも動画を削除する
     * @param type $contentobject_attribute_id
     * @param type $version
     * @param type $is_delete_main
     * @return type 
     */
    function doDelete_DB_KalturaServer($contentobject_attribute_id, $version, $is_delete_main = false) {
        // トランザクションの開始
        $db =& eZDB::instance();
        $db->begin();
        $ezkaltura_id = null;
        try{
            // データベースからkalturaデータを取得する
            $db_data_ezkaltura = eZKaltura::fetchDataByContentObjectAttributeId( $contentobject_attribute_id, $version );
            // 1.登録前に予めkalturaからデータを削除する必要がある。（kalturaサーバーにゴミファイルが溜まらないようにするための対処）
            // 2.上記の削除処理実行前に、削除しようとしているファイルが、他のバージョンにより使用されている可能性があるため、
            //   DBから取得したデータと他バージョンとを比較し、他で使用されているのか、いないのかをチェックする
            $flag_array = eZKaltura::chkKalturaMovieOtherVersion( $contentobject_attribute_id, $db_data_ezkaltura, $version );
            $delete_array = array();
            if(count($db_data_ezkaltura) > 0){
                foreach ($db_data_ezkaltura as $ezkaltura) {
                    $ezkaltura_id = $ezkaltura['kaltura_id'];
                    $entry_id = $ezkaltura['entry_id'];
                    // キーがあるかないかをチェック
                    // ある：他のバージョンで使用している
                    // ない：他のバージョンでも使用していないので、kalturaサーバーから動画を削除する
                    if (!array_key_exists($entry_id, $flag_array)) {
                        // ない
                        $delete_array[] = $entry_id;
                    }
                    if ($is_delete_main) {
                        // データベースからデータを削除（ezkalutraテーブルからデータを削除する）
                        eZKaltura::remove2kaltura_id($ezkaltura['kaltura_id']);
                    } else {
                        // データベースからデータを削除
                        // ※ ezkalutra_movieテーブルからデータを削除する。同時にezkaltura_thumbnailからもデータが削除される。
                        eZKalturaMovie::remove2id($ezkaltura['movie_id']);
                    }
                }
            }
            // 最後に他バージョンでも使用していない動画は削除する
            if (count($delete_array) > 0) {
                foreach ($delete_array as $kaltura_entry_id) {
                    // kalturaサーバーから動画を削除
                    $this->client->media->delete($kaltura_entry_id);
                }
            }
            // 正常終了
            $db->commit();
        } catch (Exception $ex) {
            // エラー：rollback
            $db->rollback();
        }
        return $ezkaltura_id;
    }
    
    /**
     * データの削除
     * 他バージョンも含め、entryidに紐づくオブジェクトは削除する
     * @param type $contentobject_attribute_id
     * @param type $version
     * @param type $entry_id
     * @return type 
     */
    function doDelete_DB_KalturaServer_With_OtherVersion($contentobject_attribute_id, $version, $entry_id, $media_type) {
        // トランザクションの開始
        $db =& eZDB::instance();
        $db->begin();
        try{
            // IDがわたってきたかどうか
            if($contentobject_attribute_id) {
                // IDがあれば、カラムからデータを削除する
                // データベースからkalturaデータを取得する
                $db_data_ezkaltura = eZKaltura::fetchDataByContentObjectAttributeId( $contentobject_attribute_id, $version );
                // 他バージョンを含め、削除しようとしている動画のIDと紐づいているカラムIDを取得する
                $movie_id_array = eZKaltura::chkKalturaMovieOtherVersion( $contentobject_attribute_id, $db_data_ezkaltura, '', true );
                foreach ($movie_id_array as $movie_id) {
                    // データベースからデータを削除
                    // ※ ezkalutra_movieテーブルからデータを削除する。同時にezkaltura_thumbnailからもデータが削除される。
                    eZKalturaMovie::remove2id($movie_id);
                }
            }
            if ($entry_id) {
                // kalturaサーバーから動画を削除
                $this->client->$media_type->delete($entry_id);
            }
            // 正常終了
            $db->commit();
        } catch (Exception $ex) {
            // エラー：rollback
            $db->rollback();
            return false;
        }
        return true;
    }    
    
    /**
     * 動画データ、サムネイルデータの登録処理
     * @param type $ezkaltura_id
     * @param type $key_entry_id
     * @param type $data_url
     * @param type $download_url
     * @param type $name
     * @param type $metadata
     * @param type $width
     * @param type $height
     * @param type $thumbnail_url 
     */
    static function doRegistKaltura($ezkaltura_id, $key_entry_id, $data_url, $download_url, $name, 
                                                $metadata, $width, $height, $thumbnail_url) {
        // トランザクションの開始
        $db =& eZDB::instance();
        $db->begin();
        try{
            // 動画情報の新規追加オブジェクトを生成
            $ezkaltura_movie = eZKalturaMovie::create(intval($ezkaltura_id), $key_entry_id, $data_url, $download_url, 
                                $name, $metadata, time(), time(), $height, $width);
            // 動画情報の保存実行
            $ezkaltura_movie->store();
            // 画像情報の新規追加オブジェクトを生成
            $ezkaltura_thumbnail = eZKalturaThumbnail::create(intval($ezkaltura_movie->ID), $thumbnail_url, time(), time());
            // 画像情報の保存実行
            $ezkaltura_thumbnail->store();
            $db->commit();
        } catch (Exception $ex) {
            // エラー：rollback
            $db->rollback();
        }
    }
    
    /**
     * データの更新を実行する
     * @param type $contentobject_attribute_id
     * @param type $version 
     */
    function doUpdateKaltura( $contentobject_attribute_id, $version ) {
        // DBからデータを取得する
        $data = eZKaltura::fetchDataByContentObjectAttributeId($contentobject_attribute_id, $version);
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                // シリアライズ化されたデータを配列へ
                $metadata = unserialize($value['serialized_metadata']);
                $data[$key]['serialized_metadata'] = $metadata;
                // ステータスを確認する
                if ($metadata->status == 1) {
                    $metadata = $this->client->media->get($value['entry_id']);
                    // 更新を実行
                    eZKalturaMovie::update( $value['movie_id'], $metadata->dataUrl, $metadata->downloadUrl, $metadata->name, 
                                                serialize($metadata), time(), $metadata->height, $metadata->width);
                }
            }
        }
        
    }
    
    /**
     * メタデータにセットされている種別を数値から文字列へ変換
     * ※ 数値から文字列に置換できるもののみ
     */
    function setMyMetadata($metadata) {
        $metadata['status'] = self::propMyStatus($metadata['status']);                               // 状態
        $metadata['mediaType'] = self::propMyMediaType($metadata['mediaType']);                      // メディア
        $metadata['sourceType'] = self::propMySourceType($metadata['sourceType']);                   // ソース
        $metadata['searchProviderType'] = self::propMyProviderType($metadata['searchProviderType']); // 検索プロバイダ
        $metadata['licenseType'] = self::propMyLicenseType($metadata['licenseType']);                // ライセンス
        $metadata['duration'] = gmdate("H:i:s", $metadata['duration']);                               // 秒を時間に変換
        $metadata['object_tag'] = $this->getObjectTag($metadata);
        return $metadata;
    }
    
    function getObjectTag($metadata) {
        // 音声は縦横値がわたってこない為、予めセットしておく必要がある
        if (!$metadata['mediaType'] || strcmp($metadata['mediaType'], 'AUDIO') == 0 || strcmp($metadata['mediaType'], 'IMAGE') == 0) {
            $metadata['width'] = 400;
            $metadata['height'] = 300;
        }
        $object_tag = '';
        $kaltura_uiconf_filter = new KalturaUiConfFilter();
        $kaltura_uiconf_filter->nameLike = 'player with no share button';
        $uiconf = $this->client->uiConf->listAction($kaltura_uiconf_filter);
        if (count($uiconf->objects) > 0) {
            $uiconf = $uiconf->objects[0];
            $data_url = $this->server_url. 'index.php/kwidget/wid/_' .$uiconf->partnerId. '/uiconf_id/' .$uiconf->id. '/entry_id/' .$metadata['id'];
            $object_tag = '
                <object name="kaltura_player" id="kaltura_player" type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" allowFullScreen="true" width="'.$metadata['width'].'" height="'.$metadata['height'].'" data="' .$data_url. '">
                    <param name="allowScriptAccess" value="always" />
                    <param name="allowNetworking" value="all" />
                    <param name="allowFullScreen" value="true" />
                    <param name="bgcolor" value="#000000" />
                    <param name="movie" value="' .$data_url. '"/>
                    <param name="wmode" value="opaque"/>
                </object>
            ';
        }
        return $object_tag;
    }
    
    /**
     * 現在の状態を取得する
     * @param type $media_type 
     */
    static function propMyStatus ($status) {
        if ($status) {
            switch ($status) {
                case KalturaEntryStatus::ERROR_CONVERTING:
                    $status = 'ERROR_CONVERTING';
                break;
                case KalturaEntryStatus::IMPORT:
                    $status = 'IMPORT';
                break;
                case KalturaEntryStatus::PRECONVERT:
                    $status = 'CONVERTING';
                break;
                case KalturaEntryStatus::READY:
                    $status = 'READY';
                break;
                case KalturaEntryStatus::DELETED:
                    $status = 'DELETED';
                break;
                case KalturaEntryStatus::PENDING:
                    $status = 'PENDING';
                break;
                case KalturaEntryStatus::MODERATE:
                    $status = 'MODERATE';
                break;
                case KalturaEntryStatus::BLOCKED:
                    $status = 'BLOCKED';
                break;
            }
        }
        return $status;
    }

    /**
     * メディアタイプを取得する
     * @param type $media_type 
     */
    static function propMyMediaType ($type) {
        if ($type) {
            switch ($type) {
                case KalturaMediaType::VIDEO:
                    $type = 'VIDEO';
                break;
                case KalturaMediaType::IMAGE:
                    $type = 'IMAGE';
                break;
                case KalturaMediaType::AUDIO:
                    $type = 'AUDIO';
                break;
            }
        }
        return $type;
    }
    
    /**
     * ソースタイプを取得する
     * @param type $media_type 
     */
    static function propMySourceType ($type) {
        if ($type) {
            switch ($type) {
                case KalturaSourceType::FILE:
                    $type = 'FILE';
                break;
                case KalturaSourceType::WEBCAM:
                    $type = 'WEBCAM';
                break;
                case KalturaSourceType::URL:
                    $type = 'URL';
                break;
                case KalturaSourceType::SEARCH_PROVIDER:
                    $type = 'SEARCH_PROVIDER';
                break;
            }
        }
        return $type;
    }
    
    /**
     * プロバイダーの種別を取得する
     * @param type $media_type 
     */
    static function propMyProviderType ($type) {
        if ($type) {
            switch ($type) {
                case KalturaSearchProviderType::FLICKR:
                    $type = 'FLICKR';
                break;        
                case KalturaSearchProviderType::YOUTUBE:
                    $type = 'YOUTUBE';
                break;        
                case KalturaSearchProviderType::MYSPACE:
                    $type = 'MYSPACE';
                break;        
                case KalturaSearchProviderType::PHOTOBUCKET:
                    $type = 'PHOTOBUCKET';
                break;        
                case KalturaSearchProviderType::JAMENDO:
                    $type = 'JAMENDO';
                break;        
                case KalturaSearchProviderType::CCMIXTER:
                    $type = 'CCMIXTER';
                break;        
                case KalturaSearchProviderType::NYPL:
                    $type = 'NYPL';
                break;        
                case KalturaSearchProviderType::CURRENT:
                    $type = 'CURRENT';
                break;        
                case KalturaSearchProviderType::MEDIA_COMMONS:
                    $type = 'MEDIA_COMMONS';
                break;        
                case KalturaSearchProviderType::KALTURA:
                    $type = 'KALTURA';
                break;        
                case KalturaSearchProviderType::KALTURA_USER_CLIPS:
                    $type = 'KALTURA_USER_CLIPS';
                break;        
                case KalturaSearchProviderType::ARCHIVE_ORG:
                    $type = 'ARCHIVE_ORG';
                break;        
                case KalturaSearchProviderType::KALTURA_PARTNER:
                    $type = 'KALTURA_PARTNER';
                break;        
                case KalturaSearchProviderType::METACAFE:
                    $type = 'METACAFE';
                break;        
                case KalturaSearchProviderType::SEARCH_PROXY:
                    $type = 'SEARCH_PROXY';
                break;
            }
        }
        return $type;
    }

    /**
     * プロバイダーの種別を取得する
     * @param type $media_type 
     */
    static function propMyLicenseType ($type) {
        if ($type) {
            switch ($type) {
                case KalturaLicenseType::UNKNOWN:
                    $type = 'UNKNOWN';
                break;
                case KalturaLicenseType::NONE:
                    $type = 'NONE';
                break;
                case KalturaLicenseType::COPYRIGHTED:
                    $type = 'COPYRIGHTED';
                break;
                case KalturaLicenseType::PUBLIC_DOMAIN:
                    $type = 'PUBLIC_DOMAIN';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION_SHARE_ALIKE:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION_SHARE_ALIKE';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION_NO_DERIVATIVES:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION_NO_DERIVATIVES';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_SHARE_ALIKE:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_SHARE_ALIKE';
                break;
                case KalturaLicenseType::CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_NO_DERIVATIVES:
                    $type = 'CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_NO_DERIVATIVES';
                break;
                case KalturaLicenseType::GFDL:
                    $type = 'GFDL';
                break;
                case KalturaLicenseType::GPL:
                    $type = 'GPL';
                break;
                case KalturaLicenseType::AFFERO_GPL:
                    $type = 'AFFERO_GPL';
                break;
                case KalturaLicenseType::LGPL:
                    $type = 'LGPL';
                break;
                case KalturaLicenseType::BSD:
                    $type = 'BSD';
                break;
                case KalturaLicenseType::APACHE:
                    $type = 'APACHE';
                break;
                case KalturaLicenseType::MOZILLA:
                    $type = 'MOZILLA';
                break;
            }
        }
        return $type;
    }
    
}
?>