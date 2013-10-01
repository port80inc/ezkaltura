<?php
class eZKalturaConfig extends eZPersistentObject
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
    *	tableのデータ型宣言
    *   `id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
    *   `server_url` varchar( 255 ) NOT NULL ,
    *   `partner_id` int( 11 ) NOT NULL ,
    *   `email` varchar( 255 ) NOT NULL ,
    *   `admin_secret` varchar( 255 ) NOT NULL ,
    *   `serialize_user` text NOT NULL ,
    *   `created` int( 11 ) unsigned NOT NULL ,
    *   `modified` int( 11 ) unsigned NOT NULL ,
    */
    static function definition()
    {
        return array( "fields"   => array(
                                        'server_url' => array( 
                                            'name'     => 'ServerUrl',
                                            'datatype' => 'string',
                                            'default'  => '',
                                            'required' => true
                                        ),
                                        'partner_id' => array( 
                                            'name'     => 'PartnerId',
                                            'datatype' => 'integer',
                                            'default'  => '',
                                            'required' => true
                                        ),
                                        'email' => array( 
                                            'name'     => 'Email',
                                            'datatype' => 'string',
                                            'default'  => '',
                                            'required' => true
                                        ),
                                        'admin_secret' => array( 
                                            'name'     => 'AdminSecret',
                                            'datatype' => 'string',
                                            'default'  => '',
                                            'required' => true
                                        ),
                                        'serialize_user' => array( 
                                            'name'     => 'SerializeUser',
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
                      'class_name' => 'eZKalturaConfig',
                      'name'       => 'ezkaltura_config'
        );
	}
    /**
     * Fetch by ID
     * 
     * @param int $id
     * @return null|eZKalturaConfig
     */

    static function fetch( $id, $limit = null, $asObject=TURE )
    {
        $cond = array( 'id' => $id );
        $rs = eZPersistentObject::fetchObject( self::definition(), null, $cond, $asObject );
        return $rs;
    }

    // 全てのデータを取得する
    static function getData()
    {
        $db = eZDB::instance();
		$query = " SELECT * FROM `ezkaltura_config` ";
		$rows = $db->arrayQuery( $query );
        if (count($rows) > 0) {
    		return $rows[0];
        }
        return false;
    }

    
    
    /*!
       データを入れる
     */
    static function create( $server_url, $partner_id, $email, $admin_secret ,$serialize_user , $created, $modified )
    {
        $db = eZDB::instance();
        $server_url = $db->escapeString($server_url);
        $partner_id = $db->escapeString($partner_id);
        $email = $db->escapeString($email);
        $admin_secret = $db->escapeString($admin_secret);
        $serialize_user = $db->escapeString($serialize_user);
        $created = $db->escapeString($created);
        $modified = $db->escapeString($modified);
        $db = eZDB::instance();
        $query = " INSERT INTO ezkaltura_config
                    (`server_url`, `partner_id`, `email`, `admin_secret`, `serialize_user`, `created`, `modified` )
                    VALUES
                    ('$server_url', '$partner_id', '$email', '$admin_secret', '$serialize_user', '$created', '$modified')
                  ";
        // データを追加する
        $db->query( $query );
    }

    /*!
       データを更新する
     */
    static function update( $server_url, $partner_id, $email, $admin_secret ,$serialize_user , $modified )
    {
        $db = eZDB::instance();
        // サニタイズ
        $server_url = $db->escapeString($server_url);
        $partner_id = $db->escapeString($partner_id);
        $email = $db->escapeString($email);
        $admin_secret = $db->escapeString($admin_secret);
        $serialize_user = $db->escapeString($serialize_user);
        $modified = $db->escapeString($modified);
        $query = " UPDATE ezkaltura_config SET
                    `server_url` = '$server_url',
                    `partner_id` = '$partner_id',
                    `email` = '$email',
                    `admin_secret` = '$admin_secret',
                    `serialize_user` = '$serialize_user',
                    `modified` = '$modified'
                  ";
        // データを追加する
        $db->query( $query );
    }

    public $ServerUrl;
    public $PartnerId;
    public $Email;
    public $AdminSecret;
    public $SerializeUser;
    public $Created;
    public $Modified;

}
?>