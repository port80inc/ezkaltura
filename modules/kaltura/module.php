<?php

//datatypeのテンプレートから呼ぶためのモジュール

$Module = array( 'name' => 'kaltura',
                 'variable_params' => true );

$ViewList = array();

//-----------------
// 属性(attribute)
//-----------------

$ViewList['browse'] = array(
    'script' => 'browse.php',
    'params' => array( 'ContentObjectID', 'ContentObjectAttributeID', 'Version', 'Mode' ),
    'functions' => array( 'read' )
);

$ViewList['db'] = array(
    'script' => 'db.php',
    'params' => array( 'ContentObjectID', 'EntryId' ),
    'functions' => array( 'read' )
);
	
$ViewList['preview'] = array(
    'script' => 'preview.php',
    'params' => array( 'ContentObjectID', 'ContentObjectAttributeID', 'movie_id' ),
    'functions' => array( 'read' )
);

//-----------------
//    管理画面
//-----------------
$ViewList['config'] = array(
    'script' => 'config.php',
    'default_navigation_part' => 'kaltura',
    'params' => array(),
    'functions' => array( 'read' )
);

$ViewList['logout'] = array(
    'script' => 'logout.php',
    'default_navigation_part' => 'kaltura',
    'params' => array(),
    'functions' => array( 'read' )
);

$ViewList['gallery_media'] = array(
    'script' => 'gallery_media.php',
    'default_navigation_part' => 'kaltura',
    'params' => array('page', 'page_size', 'view_mode'),
    'functions' => array( 'read' )
);

$ViewList['gallery_mix'] = array(
    'script' => 'gallery_mix.php',
    'default_navigation_part' => 'kaltura',
    'params' => array('page', 'page_size', 'view_mode'),
    'functions' => array( 'read' )
);

$ViewList['add_media'] = array(
    'script' => 'add_media.php',
    'default_navigation_part' => 'kaltura',
    'params' => array(),
    'functions' => array( 'read' )
);

$ViewList['add_mix'] = array(
    'script' => 'add_mix.php',
    'default_navigation_part' => 'kaltura',
    'params' => array(),
    'functions' => array( 'read' )
);

$ViewList['player'] = array(
    'script' => 'player.php',
    'default_navigation_part' => 'kaltura',
    'params' => array('entry_id'),
    'functions' => array( 'read' )
);

$ViewList['image'] = array(
    'script' => 'image.php',
    'default_navigation_part' => 'kaltura',
    'params' => array('entry_id'),
    'functions' => array( 'read' )
);

$ViewList['video_editor'] = array(
    'script' => 'video_editor.php',
    'default_navigation_part' => 'kaltura',
    'params' => array('entry_id'),
    'functions' => array( 'read' )
);

$ViewList['delete'] = array(
    'script' => 'delete.php',
    'default_navigation_part' => 'kaltura',
    'params' => array(),
    'functions' => array( 'read' )
);

$FunctionList['read'] = array();

?>
