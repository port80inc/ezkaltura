<?php
class eZKaltura extends eZPersistentObject
{

    /*!
     * Constructor
     * 
     * @param array $row
     */
    function __construct( $row )
    {
        parent::__construct( $row );
    }

   /*!
     	tableのデータ型宣言
     */
    static function definition()
    {
        return array( "fields"   => array(
                                        'id' => array(
                                            'name'     => 'ID',
                                            'datatype' => 'integer',
                                            'default'  => 1,
                                            'required' => true,
                                        ),
                                        'contentobject_attribute_id' => array(
                                            'name'                   => 'ContentObjectAttributeID',
                                            'datatype'               => 'integer',
                                            'default'                => 0,
                                            'required'               => true,
                                            'foreign_class'          => 'eZContentObjectAttribute',
                                            'foreign_attribute'      => 'id',
                                            'multiplicity'           => '1..*'
                                        ),
                                        'version'      => array( 
                                            'name'     => 'Version',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'created'      => array( 
                                            'name'     => 'Created',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'modified'     => array(
                                            'name'     => 'Modified',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => true
                                        )
									),
                      'keys'       => array( 'id' ),
                      'relations'  => array( 'contentobject_attribute_id' => array( 'class' => 'ezcontentobject_attribute',
                                                                                      'field' => 'id'),
                                              'version' => array( 'class' => 'ezcontentobject_attribute',
                                                                   'field' => 'version')),
                      'increment_key' => 'id',
                      'class_name' => 'eZKaltura',
                      'name'       => 'ezkaltura' );
	}
    /**
     * Fetch jic_main_titles by ID
     * @param int $contentobject_attribute_id
     * @param int $version  バージョン番号
     * @param int $asObject オブジェクトで取得するかどうか
     * @return null | eZFlowBlock
     */

    static function fetch( $contentobject_attribute_id, $version = null, $asObject = true )
    {
        $cond = array();
        $cond['contentobject_attribute_id'] = $contentobject_attribute_id;
        
        // バージョンの指定があれば、条件に追加する
        if($version) $cond['version'] = $version;
        
        $rs = eZPersistentObject::fetchObject( self::definition(), null, $cond, $asObject );
        return $rs;
    }

    // contentobject_idからデータを取得する
    static function getDataByContentObjectId($contentobject_id)
    {
    	if (!$contentobject_id) return false;
        $db = eZDB::instance();
		$contentobject_id = $db->escapeString($contentobject_id);
		$query = " SELECT * FROM `ezkaltura` WHERE `contentobject_id` = $contentobject_id ";
		if ($limit != null) {
			$query .= " LIMIT " . $limit['offset'] . ", " . $limit['length'];
		}
		$rows = $db->arrayQuery( $query );
		return $rows;
    }
    
    
    // contentobject_attribute_idからデータを取得する
    static function fetchDataByContentObjectAttributeId($contentobject_attribute_id, $version, $limit = null, $version_sign = '=')
    {
    	if (!$contentobject_attribute_id) return false;
        $db = eZDB::instance();
		$contentobject_attribute_id = $db->escapeString($contentobject_attribute_id);

$query = <<<EOF
    SELECT
        k.id as kaltura_id,
        k.contentobject_attribute_id as contentobject_attribute_id,
        k.version as version,
        km.id as movie_id,
        km.entry_id as entry_id,
        km.path as movie_path,
        km.download_path as download_path,
        km.height as movie_height,
        km.width as movie_width,
        km.filename as movie_filename,
        km.serialized_metadata as serialized_metadata,
        kt.id as thumbnail_id,
        kt.path as thumbnail_path
    FROM ezkaltura as k 
    LEFT JOIN ezkaltura_movie as km     ON k.`id` = km.`ezkaltura_id` 
    LEFT JOIN ezkaltura_thumbnail as kt ON km.`id` = kt.`ezkaltura_movie_id`
    WHERE k.`contentobject_attribute_id` = '$contentobject_attribute_id'
EOF;

        // バージョンの指定があるか？？
        if ($version) {
            $query .= " AND k.`version` $version_sign '$version'";
        }

		if ($limit != null) {
			$query .= ' LIMIT ' . $limit['offset'] . ', ' . $limit['length'];
		}
        
		$rows = $db->arrayQuery( $query );
		return $rows;
    }
    
    // entry_idでの条件で、データを取得する
    static function fetchDataByEntryIds($entry_ids, $limit = null)
    {
        $db = eZDB::instance();
$query = <<<EOF
    SELECT
        k.id as kaltura_id,
        k.contentobject_attribute_id as contentobject_attribute_id,
        k.version as version,
        km.id as movie_id,
        km.entry_id as entry_id,
        km.path as movie_path,
        km.download_path as download_path,
        km.height as movie_height,
        km.width as movie_width,
        km.filename as movie_filename,
        km.serialized_metadata as serialized_metadata,
        kt.id as thumbnail_id,
        kt.path as thumbnail_path
    FROM ezkaltura as k 
    LEFT JOIN ezkaltura_movie as km     ON k.`id` = km.`ezkaltura_id` 
    LEFT JOIN ezkaltura_thumbnail as kt ON km.`id` = kt.`ezkaltura_movie_id`
EOF;

        // バージョンの指定があるか？？
        if (is_array($entry_ids) && count($entry_ids) > 0) {
            $condition = ' (';
            for($i = 0; $i < count($entry_ids); ++$i) {
                if ($i > 0) $condition .= ',';
                $condition .= '"'. $entry_ids[$i]. '"';
            }
            $condition .= ') ';
            $query .= " WHERE km.`entry_id` IN $condition";
            $query .= ' ORDER BY k.`version` DESC ';
        }

		if ($limit != null) {
			$query .= ' LIMIT ' . $limit['offset'] . ', ' . $limit['length'];
		}
		$rows = $db->arrayQuery( $query );
		return $rows;
    }

    /*!
       データを入れる
     */
    static function create( $contentobject_attribute_id, $version, $created, $modified)
    {
        $row = array( 'contentobject_attribute_id' => $contentobject_attribute_id,
                        'version'                    => $version,
            		    'created'                    => $created,
            		    'modified'                   => $modified);
        return new eZKaltura( $row );
    }

    /*!
       データを削除する
     */
    static function remove2kaltura_id($id)
    {
        $db = eZDB::instance();
$query = <<<EOF
    DELETE  FROM ezkaltura WHERE id = '$id';
EOF;
        $result = $db->arrayQuery( $query );
        return $result;
    }

    /**
     * 同一オブジェクトの他バージョンで、エントリーID（kalturaサーバー内で振り分けているID）
     * が登録されていないかをチェックする
     * @param type $entry_id エントリーID（kalturaサーバー内で振り分けているID）
     * @return true or false
     */
    static function chkKalturaMovieOtherVersion($contentobject_attribute_id, $mydata, $version, $is_get_movie_id = false) {
        $return_array = array();
        // contentobject_attribute_idに紐づくデータを全て取得する
        $data_ezkaltura = eZKaltura::fetchDataByContentObjectAttributeId( $contentobject_attribute_id, $version, null, '<>');
        if (count($data_ezkaltura) > 0) {
            // 現バージョンと他バージョンのデータでチェック
            foreach ($mydata as $value1) {
                foreach ($data_ezkaltura as $value2) {
                    // エントリーIDを比較する
                    if (strcmp($value1['entry_id'], $value2['entry_id']) == 0) {
                        if ($is_get_movie_id) {
                            // 動画IDを配列へ格納する
                            $return_array[] = $value2['movie_id'];
                        } else {
                            $entry_id = $value1['entry_id'];
                            // 他のバージョンでも使っている
                            $return_array[$entry_id] = true;
                            // 次の処理へ
                            break;
                        }                        
                    }
                }
            }
        }
        return $return_array;
    }    

    public $ID;
    public $ContentObjectID;
    public $Version;
    public $Created;
    public $Modified;

}
?>