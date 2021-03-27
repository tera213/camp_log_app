<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('退会ページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

//ユーザIDを取得
$u_id = $_SESSION['user_id'];


//postが送信された場合
if(!empty($_POST)){
    debug('POSTが送信されました。');
    debug('POST:'.print_r($_POST, true));

    if(!empty($_POST['yes'])){
        debug('ユーザID'.$u_id.'は退会します。');

        try{
            //DB接続
            $dbh = dbConnect();
            //SQL文作成
            $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
            $sql2 = 'UPDATE campsite SET delete_flg = 1 WHERE user_id = :u_id';
            $data = array(":u_id" => $u_id);
            //クエリ実行
            $stmt1 = queryPost($dbh, $sql1, $data);
            $stmt2 = queryPost($dbh, $sql2, $data);

            if($stmt1 && $stmt2){
                debug('ユーザID'.$u_id.'は退会しました。');
                session_destroy();
                debug('ログインページへ遷移します。');
                header("Location:index.php");
            }
        }catch(Exception $e){
            error_log('エラー発生：'.$e -> getMessage());
        }
    }else{
        debug('退会しないのでマイページ遷移します。');
        header("Location:mypage.php");
    }
}

?>



<?php
$siteTitle = '退会';
require('head.php');
?>
<body>
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <h2>退会ページ</h2>
        <div class="form withdrawal">
            <p>退会しますか？</p>
            <form method="post">
                <input type="submit" value="はい" class="btn" name="yes">
                <input type="submit" value="いいえ" class="btn" name="no">
            </form>
        </div>
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>