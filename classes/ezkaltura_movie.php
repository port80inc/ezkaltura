<?php
class eZKalturaMovie extends eZPersistentObject
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
                                        'ezkaltura_id' => array(
                                            'name'              => 'EzKalturaID',
                                            'datatype'          => 'integer',
                                            'default'           => 0,
                                            'required'          => true,
                                            'foreign_class'     => 'eZKaltura',
                                            'foreign_attribute' => 'id',
                                            'multiplicity'      => '1..*'
                                        ),
                                        'entry_id' => array(
                                            'name'     => 'EntryID',
                                            'datatype' => 'string',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'path' => array( 
                                            'name'     => 'Path',
                                            'datatype' => 'string',
                                            'default'  => '',
                                            'required' => true
                                        ),
                                        'height' => array( 
                                            'name'     => 'Height',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => false
                                        ),
                                        'width' => array( 
                                            'name'     => 'Width',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => false
                                        ),
                                        'download_path' => array(
                                            'name'     => 'DownloadPath',
                                            'datatype' => 'string',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'filename' => array(
                                            'name'     => 'Filename',
                                            'datatype' => 'string',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'serialized_metadata' => array(
                                            'name'     => 'SerializedMetadata',
                                            'datatype' => 'string',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'created' => array( 
                                            'name'     => 'Created',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => true
                                        ),
                                        'modified' => array(
                                            'name'     => 'Modified',
                                            'datatype' => 'integer',
                                            'default'  => 0,
                                            'required' => true
                                        )
									),
                      'keys'       => array( 'id' ),

                      'relations'  => array( 'ezkaltura_id' => array( 'class' => 'eZKaltura',
                                                                        'field' => 'id' )),
                      'increment_key' => 'id',
                      'class_name' => 'eZKalturaMovie',
                      'name'       => 'ezkaltura_movie'
        );
	}
    /**
     * Fetch jic_main_titles by ID
     * 
     * @param int $id
     * @return null|eZFlowBlock
     */

    static function fetch( $id, $limit = null, $asObject=TURE )
    {
        $cond = array( 'id' => $id );
        $rs = eZPersistentObject::fetchObject( self::definition(), null, $cond, $asObject );
        return $rs;
    }

    // サブタイトルIDとオンエアーを紐づけし、データを取得する
    static function getDataByEzKalturaId($ezkaltura_id)
    {
    	if (!$ezkaltura_id) return false;
        $db = eZDB::instance();
		$ezkaltura_id = $db->escapeString($ezkaltura_id);
		$query = " SELECT * FROM `ezkaltura_movie` WHERE `ezkaltura_id` = $ezkaltura_id_id ";
		if ($limit != null) {
			$query .= " LIMIT " . $limit['offset'] . ", " . $limit['length'];
		}
		$rows = $db->arrayQuery( $query );
		return $rows;
    }

    /*!
       データを削除する
     */
    static function remove2id($id)
    {
        $db = eZDB::instance();
$query = <<<EOF
    DELETE  FROM ezkaltura_movie WHERE id = '$id';
EOF;
        $result = $db->arrayQuery( $query );
        return $result;
    }    
    
    /*!
       データを入れる
     */
    static function create( $ezkaltura_id, $entry_id, $path, $download_path, $filename, 
                                    $serialized_metadata, $created, $modified, $height = '', $width = '')
    {
        $row = array( 'ezkaltura_id'        => $ezkaltura_id,
                        'entry_id'            => $entry_id,
                        'path'                => $path,
            		    'height'              => $height,
            		    'width'               => $width,
            		    'download_path'       => $download_path,
            		    'filename'            => $filename,
            		    'serialized_metadata' => $serialized_metadata,
            		    'created'             => $created,
            		    'modified'            => $modified
                );
        return new eZKalturaMovie( $row );
    }

    /*!
       データを入れる
     */
    static function update( $id, $path, $download_path, $filename, 
                                    $serialized_metadata, $modified, $height = '', $width = '')
    {
        $db = eZDB::instance();
$query = <<<EOF
    UPDATE ezkaltura_movie SET
        path='$path',
        height='$height',
        width='$width',
        download_path='$download_path',
        filename='$filename',
        serialized_metadata='$serialized_metadata',
        modified='$modified'
    WHERE id = '$id';
EOF;
        $result = $db->arrayQuery( $query );
        return $result;

    }    
    
    public $ID;
    public $EzKalturaID;
    public $EntryID;
    public $Path;
    public $Height;
    public $Width;
    public $DownloadPath;
    public $Filename;
    public $SerializedMetadata;
    public $Created;
    public $Modified;

}
?>