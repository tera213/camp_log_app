<?php

//==================================================
//ログ出力
//==================================================

//ログ設定
ini_set('error_log', 'php.log');
ini_set('lof_errors', 'On');


//デバッグ出力関数
$debug_flg = true; //tureにしたらデバッグがログに出力される
function debug($str){
    global $debug_flg;
    if($debug_flg){
        error_log('デバッグ：'.$str);
    }
}

function debugLogStart(){
    debug('セッション情報：'.print_r($_SESSION, true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_time'] && !empty($_SESSION['loin_limit']))){
        debug('ログイン有効タイムスタンプ：'.($_SESSION['login_time'] + $_SESSION['login_limit']));
    }
}


//==================================================
//セッション有効期限の設定、使用準備
//==================================================

//セッションが消されない様に置き場を変更する
session_save_path("/var/tmp/");
//ガーベージコレクションで削除される期限を伸ばす(30日間に)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じた時にクッキーが削除されない様にクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);

session_start();
//セッションIDを置き換える（セキュリティ対策）
session_regenerate_id();


//==================================================
//定数の定義
//==================================================

//エラーメッセージをいれる配列
$err_msg = array();

//エラーメッセージ定義
define('MSG01', '入力必須項目です。');
define('MSG02', '255文字以内で入力してください。');
define('MSG03', '6文字以上で入力してください。');
define('MSG04', 'Eメールアドレスの形式で入力してください。');
define('MSG05', 'パスワードの形式で入力してください。');
define('MSG06', 'パスワード（再入力）が間違っています。');
define('MSG07', 'エラーが発生しました。しばらく経ってから再度やり直してください。');
define('MSG08', '既に登録されているEメールです。');
define('MSG09', '半角数字で入力してください。');
define('MSG10', '選択が間違っています。');
define('MSG11', 'メールアドレスまたはパスワードが間違っています。');
//成功メッセージ定義
define('SUC01', '更新しました。');
define('SUC02', '登録しました。');
define('SUC03', '削除しました。');


//==================================================
//バリデーション関数
//==================================================

//未入力チェック
function validRequired($str, $key){
    if($str == ''){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}

//最大文字数チェック
function validMaxLen($str, $key, $max = 255){
    if(mb_strlen($str) > $max){
        debug(strlen($str));
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}

//最小文字数チェック
function validMinLen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}

//Eメールアドレス形式チェック
function validEmail($str, $key){
    $reg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
    if(!preg_match($reg_str, $str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}

//Eメールアドレス重複チェック
function validEmailDup($str, $key){
    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT email FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(":email" => $str);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        //クエリ結果
        $result = $stmt -> rowCount();
        if($result == 0){
            debug('Eメール重複チエックOK');
        }else{
            debug('Eメールが重複しています。');
            global $err_msg;
            $err_msg[$key] = MSG08;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg[$key] = MSG07;

    }
}

//パスワード形式チェック(半角英数字記号(-_.))
function validPass($str, $key){
    if(!preg_match("/^[a-zA-Z0-9-_.]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}

//パスワードとパスワード（再入力チェック）
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}

//半角数字チェック
function validHalfNum($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG09;
    }
}


//都道府県選択チェック
function validPref($str, $key){
    $match_flg = false;
    for($i = 1; $i < 44; $i++){
        if($i === $str){
            $match_flg = true;
        }
    }
    if($match_flg === false){
        global $err_msg;
        $err_msg[$key] = MSG10;
    }
}

//評価のselectboxのチェック
function validSelectEval($str, $key){
    if(!preg_match("/^[0-5]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG10;
    }
}

//==================================================
//データベース
//==================================================

//DB接続関数
function dbConnect(){
    $dsn = "mysql:dbname=campDB;host=localhost;charset=utf8";
    $user = "root";
    $pass = "root";
    $option = array(
        //SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う(一度に結果セットを全て取得し、サーバ負荷を軽減)
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    //データベース接続
    $dbh = new PDO($dsn, $user, $pass, $option);
    return $dbh;
}

//SQL実行関数
function queryPost($dbh, $sql, $data){
    //クエリ作成
    $stmt = $dbh -> prepare($sql);
    //クエリ実行
    $stmt -> execute($data);
    if($stmt){
        debug('クエリ成功');
        return $stmt;
    }else{
        debug('クエリ失敗');
        debug('クエリに失敗したSQL文：'.$sql);
        global $err_msg;
        $err_msg['common'] = MSG07;
        return false;
    }
}

//ユーザ情報取得関数
function getUser($u_id){
    debug('ユーザ情報を取得します。');
    debug('ユーザID：'.$u_id);

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT u.id, u.name, u.email, u.prefectures_id, u.age, u.pic, p.name AS prefec_name FROM users AS u LEFT JOIN prefectures AS p ON u.prefectures_id = p.id WHERE u.id = :u_id AND u.delete_flg = 0';
        $data = array(":u_id" => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            return $stmt -> fetch(PDO::FETCH_ASSOC);
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//ユーザ登録関数(登録したユーザのIDを返す)
function registUser($name, $email, $pass, $prefectures_id, $age, $pic){
    debug('ユーザを登録します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'INSERT INTO users(name, email, password, prefectures_id, age, pic, create_date) VALUE(:name, :email, :password, :prefectures_id, :age, :pic, :date)';
        $data = array(":name" => $name, ":email" => $email, ":password" => $pass, ":prefectures_id" => $prefectures_id, ":age" => $age, ":pic" => $pic, ":date" => date('Y-m-d H:i:s'));
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            debug('ユーザ登録しました。');
            return $dbh -> LsatInsertId();
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//ユーザ情報更新関数
function updateUser($u_id, $name, $email, $pass, $prefectures_id, $age, $pic){
    debug('ユーザ情報を更新します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'UPDATE users SET(name = :name, email = :email, password = :password, prefectures_id = :prefectures_id, age = :age, pic = :pic WHERE id = :u_id AND delete_flg = 0';
        $data = array(":name" => $name, ":email" => $email, ":password" => $pass, ":prefectures_id" => $prefectures_id, ":age" => $age, ":pic" => $pic, "u_id" => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            debug('ユーザを更新しました。');
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//指定の1レビュー情報取得関数
function getReviewOne($r_id){
    debug('レビュー情報を取得します。');
    debug('レビューID：'.$r_id);

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT c.id, c.name, c.user_id, c.prefectures_id, c.address, c.site_name, c.price, c.pic1, c.pic2, c.pic3, c.eval_site, c.eval_bath, c.eval_cook, c.eval_shop, c.eval_location, c.eval_ave, c.comment, p.name AS pref_name 
        FROM campsite AS c INNER JOIN prefectures AS p ON c.prefectures_id = p.id WHERE c.id = :r_id AND delete_flg = 0';

        $data = array("r_id" => $r_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt -> fetch(PDO::FETCH_ASSOC);
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//都道府県名取得関数
function getPrefectures(){
    debug('都道府県名を取得します。');

    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id, name FROM prefectures';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            return $stmt -> fetchAll();
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//一覧表示用のレビュー情報取得
function getReviewList($p_num, $p_id, $sort, $span = 9){
    debug('一覧表示用のレビュー情報を取得します。');

    
    try{
        //DB接続
        $dbh = dbConnect();
        //レビュー全件数とページ数
        $sql = 'SELECT id FROM campsite WHERE delete_flg = 0';
        $data = array();
        //都道府県指定がある場合
        if(!empty($p_id)){
            $sql .= ' AND prefectures_id = :p_id';
            $data = array(":p_id" => $p_id);
        }
        //ソート指定がある場合
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY create_date DESC';
                    break;
                case 2:
                    $sql .= ' ORDER BY eval_ave DESC';
                    break;
                case 3:
                    $sql .= ' ORDER BY price ASC';
                    break;
                case 4:
                    $sql .= ' ORDER BY price DESC';
                    break;
            }
        }
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            //取得した全件数
            $result['review_all'] = $stmt -> rowCount();
            //全件数からページ数を取得
            $result['review_page'] = ceil($result['review_all'] / $span);
        }

        //一覧に表示するレビュー情報
        $sql = 'SELECT c.id, c.prefectures_id, c.name, c.price, c.pic1, c.eval_ave, p.name AS prefec_name FROM campsite AS c LEFT JOIN prefectures AS p ON c.prefectures_id = p.id WHERE delete_flg = 0';
        $data = array();
        //都道府県指定がある場合
        if(!empty($p_id)){
            $sql .= ' AND c.prefectures_id = :p_id';
            $data = array(":p_id" => $p_id);
        }
        //ソート指定がある場合
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY create_date DESC';
                    break;
                case 2:
                    $sql .= ' ORDER BY eval_ave DESC';
                    break;
                case 3:
                    $sql .= ' ORDER BY price ASC';
                    break;
                case 4:
                    $sql .= ' ORDER BY price DESC';
                    break;
            }
        }
        $sql .= ' LIMIT '.$span.' OFFSET '.( ($p_num - 1) * $span );
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //取得した全件情報取得
            $result['review_list'] = $stmt -> fetchAll();
        }
        debug('取得した情報：'.print_r($result, true));
        return $result;
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}

//マイページ表示用のレビュー情報を取得する
function getReviewMyList($p_num, $u_id, $span = 9){
    debug('マイページ用のレビュー情報を取得します。');
    try{
        //DB接続
        $dbh = dbConnect();
        //レビュー全件数とページ数
        $sql = 'SELECT id FROM campsite WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(":u_id" => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            //取得した全件数
            $result['review_all'] = $stmt -> rowCount();
            //全件数からページ数を取得
            $result['review_page'] = ceil($result['review_all'] / $span);
        }

        //一覧に表示するレビュー情報
        $sql = 'SELECT c.id, c.prefectures_id, c.name, c.price, c.pic1, c.eval_ave, p.name AS prefec_name FROM campsite AS c LEFT JOIN prefectures AS p ON c.prefectures_id = p.id WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(":u_id" => $u_id);
        $sql .= ' LIMIT '.$span.' OFFSET '.( ($p_num - 1) * $span );
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //取得した全件情報取得
            $result['review_list'] = $stmt -> fetchAll();
        }
        debug('取得した情報：'.print_r($result, true));
        return $result;
    }catch(Exception $e){
        error_log('エラー発生：'.$e -> getMessage());
        global $err_msg;
        $err_msg['common'] = MSG07;
    }
}



//==================================================
//画像
//==================================================

//画像アップロード
function uploadImg($file, $key){ //第一引数にFILES['file_name']を指定
    debug('画像アップロード処理を開始します。');
    debug('画像情報：'.print_r($file, true));

    //エラーの要素に値があるかチェック
    if(isset($file['error'])){
        try{
            switch($file['error']){
                case UPLOAD_ERR_OK: //エラーなし、ファイルあり
                    break;
                case UPLOAD_ERR_FILE: //ファイルがアップロードされていない
                    throw new RuntimeException('ファイルが選択されていません。');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('ファイルサイズが大きすぎます。');
                default:
                    throw new RuntimeException('その他のエラーです。');
            }
            debug('エラーチェックOK');
            //MIMEタイプを取得
            $type = @exif_imagetype($file['tmp_name']);
            //拡張子のチェック
            if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
                throw new RuntimeException('アップロードできない拡張子が含まれています。');
            }
            debug('拡張子チェックOK');
            debug('拡張子：'.print_r($type, true));

            //パス生成
            $path = "uploads/".sha1_file($file['tmp_name']).image_type_to_extension($type, true);
            debug('生成パス：'.$path);

            //アップロード
            if(!move_uploaded_file($file['tmp_name'], $path)){
                throw new RuntimeException('アップロードに失敗しました。');
            }
            chmod($path, 0644);
            debug('ファイルのアップロードが完了しました。');
            debug('アップロードしたファイルパス：'.$path);

            return $path;

        }catch(RuntimeException $e){
            error_log('エラー発生：'.$e -> getMessage());
            global $err_msg;
            $err_msg[$key] = $e -> getMessage();
        }
    }
} 


//==================================================
//その他
//==================================================

//サニタイズ
function sanitize($str){
    return htmlspecialchars($str, ENT_QUOTES);
}

//フォームデータ保持
function getFormData($str, $flg = true){
    global $dbFormData;
    //$flgがtrueの場合はPOST
    if($flg){
        //POSTデータがある場合
        if(!empty($_POST[$str])){
            return $_POST[$str];
        }else{
            //POSTデータがない場合
            return (!empty($dbFormData[$str])) ? $dbFormData[$str] : '';
        }
    }else{
        //GETデータがある場合
        if(!empty($_GET[$str])){
            return $_GET[$str];
        }else{
            //GETデータがない場合
            return (!empty($dbFormData[$str])) ? $dbFormData[$str] : '';
        }
    }
}

//画像表示関数
function showImg($path){
    if(!empty($path)){
        return "image/sample-img.png";
    }else{
        return $path;
    }
}

//GETパラメータ付与関数
function appendGetParam($attr_del_key = array()){
    if(!empty($_GET)){
        $str = '?';
        foreach($_GET as $key => $val){
            if(!in_array($key, $attr_del_key, true)){
                $str .= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");
        return $str;
        
    }
    
}

//スライドメッセージ表示
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $str = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $str;
    }
}