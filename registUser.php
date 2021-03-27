<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('ユーザ新規登録');
debug('********************************************');
debugLogStart();

//都道府県データ取得
$dbPrefucData = getPrefectures();

//POST送信があった場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILES情報：'.print_r($_FILES, true));

    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];
    $prefectures_id = $_POST['prefectures_id'];
    $age = (!empty($_POST['age']))? $_POST['age']: '0';
    
    
    //必須項目の未入力チェック
    validRequired($name, 'name');
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($pass_re, 'pass_re');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        validMaxLen($name, 'name');

        validMaxLen($email, 'email');
        validEmail($email, 'email');
        validEmailDup($email, 'email');

        validPass($pass, 'pass');
        validMaxLen($pass, 'pass');
        validMinLen($pass, 'pass');

        //validPref($prefectures_id, 'prefectures_id');

        validHalfNum($age, 'age');

        if(empty($err_msg)){
            debug('バリデーションOK;');

            validMatch($pass, $pass_re, 'pass_re');

            if(empty($err_msg)){
                debug('パスワード再入力のチェックOK');
                //画像があればアップロードする処理
                $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
                try{
                    debug('ユーザ登録処理を開始します。');
                    //DB接続
                    $dbh = dbConnect();
                    //SQL文作成
                    $sql = 'INSERT INTO users(name, email, password, prefectures_id, age, pic, create_date) VALUE(:name, :email, :password, :prefectures_id, :age, :pic, :date)';
                    $data = array(":name" => $name, ":email" => $email, ":password" => password_hash($pass, PASSWORD_DEFAULT), ":prefectures_id" => $prefectures_id, ":age" => $age, ":pic" => $pic, ":date" => date('Y-m-d H:i:s'));
                    //クエリ実行
                    $stmt = queryPost($dbh, $sql, $data);
            
                    if($stmt){
                        debug('ユーザを登録しました。');
                        
                        //セッションにIDをいれる
                        $_SESSION['user_id'] = $dbh -> LastInsertId();
                        //セッションにログイン日時をいれる
                        $_SESSION['login_time'] = time();
                        //セッションにログイン有効期限をいれる
                        $_SESSION['login_limit'] = 60 * 60;

                        debug('セッション情報：'.print_r($_SESSION, true));
                        debug('マイページへ遷移します。');
                        header("Location:mypage.php");

                    }
                }catch(Exception $e){
                    error_log('エラー発生：'.$e -> getMessage());
                    global $err_msg;
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
?>

<?php
$siteTitle = '新規登録';
require('head.php');
?>

<body>
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <h2>新規登録</h2>
        
        <div class="form">
            <form method="post" enctype="multipart/form-data">
                <p class="caution"><span class="required-mark">※</span>は必須入力項目です。</p>
                <label>
                    <div class="dropIcon-area">
                        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                        <input class="img-file" type="file" name="pic">
                        <img class="icon-img" src="<?php echo (!empty($_FILES['pic']['name'])) ? $_FILES['pic']['tmp_name'] : "image/初期アイコン.png"; ?>" alt="">
                    </div>
                    <p class="dropIcon-text">画像を編集</p>
                    <div class="msg-area">
                        <?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; ?>
                    </div>
                </label>
                <label>
                    <p>氏名<span class="required-mark">※</span></p>
                    <input class="<?php if(!empty($err_msg['name'])) echo 'err' ?>" type="text" name="name" value="<?php echo getFormData('name'); ?>">
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
                </div>
                <label>
                    <p>年齢</p>
                    <input class="<?php if(!empty($err_msg['age'])) echo 'err' ?>" type="number" name="age" value="<?php echo getFormData('age'); ?>">
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
                </div>
                <label>
                    <p>都道府県</p>
                    <select name="prefectures_id">
                        <option value="0" selected>選択してください</option>
                        <?php
                            foreach($dbPrefucData as $key => $val){
                        ?>
                                <option value="<?php echo $val['id'] ?>" 
                                <?php
                                    if(!empty($_POST["prefectures_id"])){
                                        if($_POST["prefectures_id"] == $val['id']) echo 'selected';
                                    }
                                ?>
                                ><?php echo $val['name'] ?></option>
                        <?php
                            }
                        ?>
                    </select>
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['prefectures_id'])) echo $err_msg['prefectures_id']; ?>
                </div>
                <label>
                    <p>メールアドレス<span class="required-mark">※</span></p>
                    <input class="<?php if(!empty($err_msg['email'])) echo 'err' ?>" type="text" name="email" value="<?php echo getFormData('email'); ?>">
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                </div>
                <label>
                    <p>パスワード<span class="required-mark">※</span></p>
                    <input class="<?php if(!empty($err_msg['pass'])) echo 'err' ?>" type="password" name="pass" value="<?php echo getFormData('pass'); ?>">
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                </div>
                <label>
                    <p>パスワード(再入力)<span class="required-mark">※</span></p>
                    <input class="<?php if(!empty($err_msg['pass_re'])) echo 'err' ?>" type="password" name="pass_re" value="<?php echo getFormData('pass_re'); ?>">
                </label>
                <div class="msg-area">
                    <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
                </div>
                <label>
                    <input type="submit" value="登録" class="btn regist-btn">
                </label>
            </form>
        </div>
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>