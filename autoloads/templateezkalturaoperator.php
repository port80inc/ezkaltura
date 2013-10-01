<?php

/*!
  \class   TemplateKalturaOperator templatekalturaoperator.php
  \ingroup eZTemplateOperators
  \brief   テンプレートオペレータ kalturaのハンドラ。 
  \version 1.0
  \date    2010年 11月 25日 木曜日 14:09:55 pm
  \author  Port80 Inc. Nanba

  kalturaのセッションを取得

*/
require_once("extension/ezkaltura/lib/KalturaClient.php");


class TemplateEzKalturaOperator
{
    function TemplateEzKalturaOperator()
    {
    }

    function operatorList()
    {
        return array( 'get_kaltura_session_key', 'get_env_server' );
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 'get_kaltura_session_key'	 => array( 
        									'partner_id'   => array( 'type' => 'integer',
																	 'required' => true ),
        									'server_url'   => array( 'type' => 'string',
																	 'required' => true ),
        									'admin_secret' => array( 'type' => 'string',
																	 'required' => true ) ),
        			  'get_env_server'   => array(
        									'server_key'       => array( 'type' => 'string',
																	 'required' => true )),
               );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
        switch ( $operatorName )
        {
            case 'get_kaltura_session_key':
            {
				$config = new KalturaConfiguration($namedParameters['partner_id']);
				$config->serviceUrl = $namedParameters['server_url'];
				$client = new KalturaClient($config);
				$operatorValue = $client->session->start($namedParameters['admin_secret'], "USERID", KalturaSessionType::ADMIN,$namedParameters['partner_id']);
				break;
			}
            case 'get_env_server':
            {
            	$return = array('env_data' => '', 'current_site_access' => '');
            	if (array_key_exists ($namedParameters['server_key'], $_SERVER)) {
            		$server_key = $namedParameters['server_key'];
            		// サーバーの値を取得する
            		$return['env_data'] = $_SERVER[$server_key];
            	}
            	if ( array_key_exists('eZCurrentAccess', $GLOBALS) && array_key_exists('name', $GLOBALS['eZCurrentAccess'])) {
            		$server_key = $namedParameters['server_key'];
            		// サーバーの値を取得する
            		$return['current_site_access'] = $GLOBALS['eZCurrentAccess']['name'];
            	}
				$operatorValue = $return;
				break;
			}
        }
    }

}
