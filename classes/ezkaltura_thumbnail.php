<?php
class eZKalturaThumbnail extends eZPersistentObject
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
                                        'ezkaltura_movie_id'          => array(
                                            'name'              => 'EzKalturaMovieID',
                                            'datatype'          => 'integer',
                                            'default'           => 0,
                                            'required'          => true,
                                            'foreign_class'     => 'eZKaltura',
                                            'foreign_attribute' => 'id',
                                            'multiplicity'      => '1..*'
                                        ),
                                        'path' => array( 
                                            'name'     => 'Path',
                                            'datatype' => 'string',
                                            'default'  => '',
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

                      'relations'  => array( 'ezkaltura_movie_id' => array( 'class' => 'eZKalturaMovie',
                                                                        'field' => 'id' )),
                      'increment_key' => 'id',
                      'class_name' => 'eZKalturaThumbnail',
                      'name'       => 'ezkaltura_thumbnail'
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
    static function getDataByEzKalturaId($ezkaltura_movie_id)
    {
    	if (!$ezkaltura_movie_id) return false;
        $db = eZDB::instance();
		$ezkaltura_movie_id = $db->escapeString($ezkaltura_movie_id);
		$query = " SELECT * FROM `ezkaltura_thumbnail` WHERE `ezkaltura_movie_id` = $ezkaltura_movie_id ";
		if ($limit != null) {
			$query .= " LIMIT " . $limit['offset'] . ", " . $limit['length'];
		}
		$rows = $db->arrayQuery( $query );
		return $rows;
    }

    
    
    /*!
       データを入れる
     */
    static function create( $ezkaltura_movie_id, $path, $created, $modified)
    {
        $row = array( 'ezkaltura_movie_id'  => $ezkaltura_movie_id,
                        'path'                => $path,
            		    'created'             => $created,
            		    'modified'            => $modified
                );
        return new eZKalturaThumbnail( $row );
    }

    public $ID;
    public $EzKalturaID;
    public $EzKalturaMovieID;
    public $Path;
    public $Created;
    public $Modified;

}
?>