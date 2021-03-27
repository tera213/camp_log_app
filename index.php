<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('ログインページ');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');
//POST送信があった場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST:'.print_r($_POST, true));

    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $save = $_POST['save'];

    //必須項目の未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        //Eメールのバリデーションチェック
        // validMaxLen($email, 'email');
        // validEmail($email, 'email');

        //パスワードのバリデーションチェック
        // validMaxLen($pass, 'pass');
        // validMinLen($pass, 'pass');
        // validPass($pass, 'pass');

        if(empty($err_msg)){
            debug('各入力値のバリデーションOK');

            try{
                //DB接続
                $dbh = dbConnect();
                //SQL文作成
                $sql = 'SELECT id, email, password FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(":email" => $email);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                $result = $stmt -> fetch(PDO::FETCH_ASSOC);
                debug(print_r($result, true));
                //パスワードが一致するかチェック
                if(password_verify($pass, $result['password'])){
                    debug('パスワードが一致しました。');

                    //セッションにIDをいれる
                    $_SESSION['user_id'] = $result['id'];
                    //セッションにログインしたタイムスタンプをいれる
                    $_SESSION['login_time'] = time();
                    //有効期限の設定(デフォルトは1時間)
                    $seslimit = 60 * 60;

                    if($save){ //30日間ログイン保持にチェックがついていた場合
                        $seslimit = 60 * 60 * 24 * 30;
                    }
                    //セッションに有効期限をいれる
                    $_SESSION['login_limit'] = $seslimit;
                    debug('セッション情報：'.print_r($_SESSION, true));

                    debug('マイページへ遷移します。');
                    header("Location:mypage.php");
                }else{
                    debug('パスワードが間違っています。');
                    $err_msg['common'] = MSG11;
                }
            }catch(Exception $e){
                error_log('エラー発生:'.$e -> getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}

?>

<?php
$siteTitle = 'ログイン';
require('head.php');
?>

<body>
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <h2>ログイン</h2>
        <div class="form">
            <form method="post">
                <div class="msg-area">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <input type="text" name="email" value="<?php echo getFormData('email'); ?>" placeholder="メールアドレス">
                <div class="msg-area">
                    <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                </div>
                <input type="password" name="pass" value="<?php echo getFormData('pass'); ?>" placeholder="パスワード">
                <div class="msg-area">
                    <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                </div>
                <label class="save-check">
                    <input type="checkbox" name="save" value="true"><span>ログイン状態を保持する</span>
                </label>
                <input type="submit" value="ログイン" class="btn">
            </form>
            <p><a href="">パスワード再発行</a></p>
            <div class="regist">
                <h2>まだ登録されていない方</h2>
                <a href="registUser.php" class="btn-link">新規登録</a>
            </div>
        </div>
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>
</body>
</html>