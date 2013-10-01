<?php

require_once('extension/ezkaltura/classes/ezkaltura_comm.php');
/**
 * kalturaとの連携用
 * 
 * @uses       eZDataType
 * @package    ezkaltura
 * @subpackage ezkaltura
 * @version    
 * @author     
 * @license    
 */

require_once('kernel/common/i18n.php');

class ezKalturaType extends eZDataType
{
    const DATATYPE_STRING = "ezkaltura";

    /**
     * construct.
     */
    public function __construct()
    {
        parent::__construct( self::DATATYPE_STRING,
                              ezi18n( 'kernel/classes/datatypes',
                                      "Kaltura",
                                      'Datatype name' ),
                              array( 'serialize_supported'  => true,
                                     'object_serialize_map' => array( 'data_text' => 'kaltura_url' )) );
    }

    /**
     * データの本編集に入る前に、バージョンを元にあらかじめデータを生成する
     * ※以下、postInitializeObjectAttribute()は、オブジェクト【編集】ボタン押下時に処理が走る
     * @param type $contentObjectAttribute         次バージョンのオブジェクトデータが格納された変数
     * @param type $currentVersion                 現在のバージョン
     * @param type $originalContentObjectAttribute 現在のバージョンのオブジェクトデータが格納された変数
     */
	function postInitializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
	{
		// 現状のバージョンデータが存在するか？
		if ( $currentVersion != false )
		{
			// 存在する
			$contentObjectAttributeID = $originalContentObjectAttribute->attribute( "id" );

            // 次バージョンの値を取得する
            $version = $contentObjectAttribute->attribute( "version" );

   			// 次バージョンのデータがすでに存在しているかどうかをチェック
			$newfile = eZKaltura::fetchDataByContentObjectAttributeId( $contentObjectAttribute->attribute( 'id' ), $version );
            // 未だデータが存在しなければ、次バージョンのデータを作っておく必要がある
            if (count($newfile) == 0) {
                // 新しいバージョンでデータを作成
                $ezkaltura = eZKaltura::create( $contentObjectAttribute->attribute( 'id' ), $version, time(), time() );

                // 保存を実行
                $ezkaltura->store();
                // 公開中のIDとバージョンからデータを取得する
                $oldfile = eZKaltura::fetchDataByContentObjectAttributeId( $contentObjectAttributeID, $currentVersion );

                if( $oldfile != null ) {
                    // 動画、画像のデータを作成するバージョン分登録する
                    foreach($oldfile as $value) {
                        // 画像の登録があれば、以下の保存処理を走らせる
                        if ($value['movie_id']) {
                            // データを登録する
                            eZKalturaComm::doRegistKaltura($ezkaltura->ID, $value['entry_id'], $value['movie_path'], $value['download_path'],
                                                            $value['movie_filename'], $value['serialized_metadata'], $value['movie_width'], $value['movie_height'], $value['thumbnail_path']);
                        }

                    }
                }
			}
		} else {
			// データが存在しない
			$contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );
			$version = $contentObjectAttribute->attribute( 'version' );
            // 日時を取得する:登録日、更新日
            $time = time();
			// データが存在しない為、新たにデータを生成する
			$ezkaltura = eZKaltura::create( $contentObjectAttributeID, $version, $time, $time );
            // 保存実行
			$ezkaltura->store();
        }
    }

    /*!
     Delete stored attribute
    */
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
		// contentObjectIDを取得
        $contentobject_id = intval( $contentObjectAttribute->attribute( "contentobject_id" ) );
		// contentObjectAttributeIDを取得
		$contentobject_attribute_id = intval( $contentObjectAttribute->attribute( "id" ) );
        // インスタンス作成
        $ez_kaltura_comm = new eZKalturaComm($contentobject_id, $contentobject_attribute_id);
        if ($ez_kaltura_comm->client) {
            // 削除を実行
            $ez_kaltura_comm->doDelete_DB_KalturaServer($contentobject_attribute_id, $version, true);
        }
    }

    /**
     * 編集で登録されているkalturaのデータからサーバーへ問い合わせを行い、最新のデータを取得、DBへ再登録（動画生成済みのものはスルーする）
     * ※以下、fetchObjectAttributeHTTPInput()は、編集画面の【送信して公開】ボタン押下時に処理が走る
     * @param type $http
     * @param type $base
     * @param type $contentObjectAttribute
     * @return type 
     */
    public function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        // バージョンを取得する
        $version = $contentObjectAttribute->Version;
		// contentObjectIDを取得
        $contentobject_id = intval( $contentObjectAttribute->attribute( "contentobject_id" ) );
		// contentObjectAttributeIDを取得
		$contentobject_attribute_id = intval( $contentObjectAttribute->attribute( "id" ) );
        // インスタンス作成
        $ez_kaltura_comm = new eZKalturaComm($contentobject_id, $contentobject_attribute_id);
        if ($ez_kaltura_comm->client) {
            // 更新処理を行う
            $ez_kaltura_comm->doUpdateKaltura($contentObjectAttribute->ID, $version);
            return true;
        }
        return false;
    }

    /**
     * Validates the input from the object edit form concerning this attribute.
     *
     * @param mixed  $http                   Class eZHTTPTool.
     * @param string $base                   Seems to be always 'ContentObjectAttribute'.
     * @param mixed  $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return int eZInputValidator::STATE_INVALID/STATE_ACCEPTED
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        
        if ( $http->hasPostVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( 'id' ) ))
        {

 			$kaltura_url = $http->postVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( "id" ) );
            if($contentObjectAttribute->attribute("is_required") && ($kaltura_url=="") )
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
            return eZInputValidator::STATE_ACCEPTED;
        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Stores the object attribute input in the $contentObjectAttribute.
     *
     * @param mixed  $http                   Class eZHTTPTool.
     * @param string $base                   Seems to be always 'ContentObjectAttribute'.
     * @param mixed  $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return boolean Whether to save the changes to the db or not.
     */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $kaltura_url = $http->postVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( "id" ) );
            $collectionAttribute->setAttribute( "data_text", $kaltura_url );
            return true;
        }
        return false;
    }

    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $kaltura_url = $http->postVariable( $base . '_kaltura_url_' . $contentObjectAttribute->attribute( "id" ) );

            if($contentObjectAttribute->attribute("is_required") && ($kaltura_url=="" ) )
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }

        }
        return eZInputValidator::STATE_INVALID;
    }

    /**
     * Handles the input specific for one attribute from the class edit interface.
     *ezcontentclass_attributeテーブルに以下を設定する
     * data_int1---パートナーID
     * data_text1--kalturaurl
     * data_text2--email
     * data_text3--シークレットキー
     * data_text4--admin シークレットキー
     * @param mixed  $http           Class eZHTTPTool.
     * @param string $base           Seems to be always 'ContentClassAttribute'.
     * @param mixed  $classAttribute Class eZContentClassAttribute.
     *
     * @return boolean
     */
    public function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {

		// partner_id, email,passwordから、kalturaサーバーよりシークレットキー、
		// adminシークレットキーを取得して contentclass_attributeテーブルに

		//partner_id取得

		if ( $http->hasPostVariable( $base . "_ezkaltura_partner_id_" . $classAttribute->attribute( "id" ) ))
        {
            $partner_id = trim($http->postVariable( $base . "_ezkaltura_partner_id_" . $classAttribute->attribute( "id" ) ));


			if ($partner_id > 0 && is_numeric($partner_id)) {
				$classAttribute->setAttribute( "data_int1", $partner_id);
			} else {
				$partner_id = '';
				$classAttribute->setAttribute( "data_int1", 0);

			}
		}
		//kaltura server url取得


		if ( $http->hasPostVariable( $base . "_ezkaltura_server_url_" . $classAttribute->attribute( "id" ) ))
        {
            $server_url = trim($http->postVariable( $base . "_ezkaltura_server_url_" . $classAttribute->attribute( "id" ) ));

			$classAttribute->setAttribute( "data_text1", $server_url);

		}

		//email取得

		if ( $http->hasPostVariable( $base . "_ezkaltura_email_" . $classAttribute->attribute( "id" ) ))
        {
            $email = trim($http->postVariable( $base . "_ezkaltura_email_" . $classAttribute->attribute( "id" ) ));

			$classAttribute->setAttribute( "data_text2", $email);
		}
		//password取得

		if ( $http->hasPostVariable( $base . "_ezkaltura_password_" . $classAttribute->attribute( "id" ) ))
        {
            $password = trim($http->postVariable( $base . "_ezkaltura_password_" . $classAttribute->attribute( "id" ) ));
		}

		//パスワードを入れた場合のみ変更できる
		if ($password != "" && $partner_id != "" && $email != "") {
			$config = new KalturaConfiguration($partner_id);
			$config->serviceUrl = $server_url;
			$client = new KalturaClient($config);
			$PartnerService = new KalturaPartnerService($client);
			$admin_secret = $PartnerService->getSecrets($partner_id, $email, $password);
            
			if ($admin_secret) {
				$classAttribute->setAttribute( "data_text3", $admin_secret->secret);
				$classAttribute->setAttribute( "data_text4", $admin_secret->adminSecret);
			} else {
				$classAttribute->setAttribute( "data_text3", '');
				$classAttribute->setAttribute( "data_text4", '');
			}
		} else {
			//secret取得

			if ( $http->hasPostVariable( $base . "_ezkaltura_secret_" . $classAttribute->attribute( "id" ) ))
            {
                	$secret = trim($http->postVariable( $base . "_ezkaltura_secret_" . $classAttribute->attribute( "id" ) ));
					$classAttribute->setAttribute( "data_text3", $secret);

			}

			//admin secret取得

			if ( $http->hasPostVariable( $base . "_ezkaltura_admin_secret_" . $classAttribute->attribute( "id" ) ))
            {
                $admin_secret = trim($http->postVariable( $base . "_ezkaltura_admin_secret_" . $classAttribute->attribute( "id" ) ));
				$classAttribute->setAttribute( "data_text4", $admin_secret);
			}
			
		}

        return true;
    }

    /**
     * Returns the content object of the attribute.
     *
     * @param mixed $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return array
     */
    public function objectAttributeContent( $contentObjectAttribute )
    {
        // contentobject_idを取得
    	$contentobject_id = intval($contentObjectAttribute->attribute('contentobject_id'));
        
        // contentobject_attribute_idを取得
    	$contentobject_attribute_id = intval($contentObjectAttribute->attribute('id'));
        // versionを取得
    	$version = intval($contentObjectAttribute->attribute('version'));
        
        // インスタンス作成
        $ez_kaltura_comm = new eZKalturaComm($contentobject_id, $contentobject_attribute_id);
        if ($ez_kaltura_comm->client) {
            // 予め、ステータスが変換中のものについて、更新処理を行っておく
            $ez_kaltura_comm->doUpdateKaltura($contentobject_attribute_id, $version);
        }
        
        // DBから動画データを取得
        $db_data_ezkaltura = eZKaltura::fetchDataByContentObjectAttributeId( $contentobject_attribute_id, $version );
		if (count($db_data_ezkaltura) > 0) {
            foreach($db_data_ezkaltura as $key => $value) {
                // シリアライズ化されたデータを復元し、配列にキャストする
                $db_data_ezkaltura[$key]['serialized_metadata'] = $ez_kaltura_comm->setMyMetadata((array)unserialize($value['serialized_metadata']));
            }
		}
        return $db_data_ezkaltura;
    }

    /**
     * Returns the meta data used for storing search indeces.
     * 
     * @param mixed $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return string
     */
    public function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /**
     * Returns a string that could be used for the object title.
     *
     * @param mixed $contentObjectAttribute ContentObjectAttribute.
     * @param mixed $name                   No idea...
     *
     * @return string
     */
    public function title( $contentObjectAttribute, $name = null )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /**
     * Returns whether the attribute contains data.
     *
     * @param mixed $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return boolean
     */
    public function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( "data_text" ) ) != '';
    }

    /**
     * IsIndexable.
     *
     * @return boolean
     */
    public function isIndexable()
    {
        return true;
    }

    /**
     * IsInformationCollector.
     *
     * @return boolean
     */
    function isInformationCollector()
    {
        return true;
    }

    /**
     * Returns a key to sort attributes.
     *
     * @param mixed $contentObjectAttribute Class eZContentObjectAttribute.
     *
     * @return string
     */
    public function sortKey( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /**
     * Returns the type of the sortKey.
     *
     * @return string
     */
    public function sortKeyType()
    {
        return 'string';
    }

}
eZDataType::register( ezKalturaType::DATATYPE_STRING, "ezKalturaType" );

?>
