<?php
/**
 * ログイン処理
 * 2011-06-24
 * itou
 */

require_once( 'kernel/common/template.php' );
require_once('extension/ezkaltura/classes/ezkaltura_comm.php');
require_once('extension/ezkaltura/classes/ezkaltura_config.php');
require_once("extension/ezkaltura/lib/KalturaClient.php");

//-------------
// 前フィルター
//-------------
// キャッシュをクリアする
eZContentCacheManager::clearAllContentCache(true);


//-------------
//   通常処理
//-------------
$http = eZHTTPTool::instance();
$data = eZKalturaConfig::getData();

$tpl = templateInit();
$Result = array();

// エラーメッセージ格納用変数
$error = '';
$is_success = false;

// 初期値を含め、パラメータを取得する
$params = getParameters($_POST);

// メソッドを確認 == POSTであれば次の工程へ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // バリデーションを行う
    $error = isValid($params);
    // エラーの有無を確認
    if (count($error) == 0) {
        // エラーなし：認証処理を実行
        // 入力されたID、パスワード等で認証が通るかどうか
        if ($kaltura_partner_object = isMyAccount($params)) {
            if (!$data) {
                // データを登録する
                eZKalturaConfig::create($params['server_url'], $params['partner_id'], $params['email'], $kaltura_partner_object->adminSecret, serialize((array)$kaltura_partner_object), time(), time());
            } else {
                // データを更新する
                eZKalturaConfig::update($params['server_url'], $params['partner_id'], $params['email'], $kaltura_partner_object->adminSecret, serialize((array)$kaltura_partner_object), time());
            }
            $data = eZKalturaConfig::getData();
            $is_success = true;
        } else {
            $error[] = 'The account does not exist.Please check what you type.';
        }
    }
} else {
    // DBに登録されているデータで上書きする
    $params = $data;
}



// テンプレートアサイン
$tpl->setVariable( 'error'     , $error);
$tpl->setVariable( 'params'    , $params);
$tpl->setVariable( 'is_success', $is_success);
// 出力テンプレート、タイトルを指定
if (!$data) {
    // データがないので、設定項目のみメニューに出力する
    $Result['left_menu'] = 'design:kaltura/left_menu_no_data.tpl';
    $tpl->setVariable( 'mode', 'add');
} else {
    $Result['left_menu'] = 'design:kaltura/left_menu.tpl';
    $tpl->setVariable( 'mode', 'edit');
}
$Result['content'] = $tpl->fetch( 'design:kaltura/config.tpl');
$Result['path'] = array( array( 'url' => false,
                     'text' => 'Kaltura Login' ) );

//------
// 関数
//------

/**
 * ポストデータから入力データを取得する
 * @param array $params
 * @return array 
 */
function getParameters($params) {
    // 返り値格納用
    $return = array();
    // 以下、配列を確認し、キーの存在するものに関しては、配列へ代入していく
    $return['server_url'] = array_key_exists('server_url', $params) ? trim($params['server_url']) : '';  // サーバーURL
    $return['partner_id'] = array_key_exists('partner_id', $params) ? trim($params['partner_id']) : '';  // partner_id
    $return['email']      = array_key_exists('email', $params)      ? trim($params['email']) : '';       // メールアドレス
    $return['password']   = array_key_exists('password', $params)   ? trim($params['password']) : '';    // パスワード
    
    return $return;
   
}

/**
 * 登録アカウントの確認
 * @param type $params
 * @return type 
 */
function isMyAccount($params) {
    // 設定項目をセット
    $config = new KalturaConfiguration($params['partner_id']);
    $config->serviceUrl = $params['server_url'];
    $client = new KalturaClient($config);
    $kaltura_partner_service = new KalturaPartnerService($client);
    // シークレットキーを取得する
    $kaltura_partner_object = $kaltura_partner_service->getSecrets($params['partner_id'], $params['email'], $params['password']);
    // データが存在するか？
    if($kaltura_partner_object) {
        return $kaltura_partner_object;
    }
    return false;
}

/**
 * 入力チェックを行う
 * @param type $params 
 * @return array
 */ 
function isValid($params) {
    $return_errors = array();
    $error_messages = getErrormessages();
    // 一通りの空チェックを行う。
    foreach ($params as $key => $value) {
        // 空チェック
        if (!$value) {
            // キーに紐づくエラーを配列へ
            array_push($return_errors, str_replace('%NAME%', $key, $error_messages['empty']));
        }
    }
    // URLが書式通りかどうか
    if ($params['server_url'] && !isURL($params['server_url'])) {
        array_push($return_errors, $error_messages['url']);
    }
        
    // メールアドレスの書式を確認
    if ($params['email'] && !isEmail($params['email'])) {
        array_push($return_errors, $error_messages['email']);
    }
    return $return_errors;
}

/**
 * エラーメッセージを返す
 * @return type 
 */
function getErrormessages() {
    $error_messages = array(
                            'empty'  => 'Please enter your %NAME%.',
                            'url'    => 'Please check the URL format.',
                            'email'  => 'Please check the email format.'
                        );
    return $error_messages;
}

/**
 * URLの書式を確認
 * @param type $url
 */
function isURL($url) {
    if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url)) {
        return true;
    }
    return false;
}

/**
 * メールアドレスの書式を確認
 * @param type $email
 */
function isEmail($email) {
    // メールアドレスチェック
    if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)) {
        return true;
    }
    return false;
}

?>