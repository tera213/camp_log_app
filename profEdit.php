<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('プロフィール編集ページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');


//ユーザID
$u_id = '';
//ユーザ情報
$dbFormData = '';
//都道府県情報
$dbPrefucData = '';


//ユーザIDを取得
$u_id = $_SESSION['user_id'];
//ユーザ情報を取得
$dbFormData = getUser($u_id);
debug('ユーザ情報：'.print_r($dbFormData, true));
//都道府県データ取得
$dbPrefucData = getPrefectures();


//POST送信がある場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILES情報：'.print_r($_FILES, true));

    $name = $_POST['name'];
    $email = $_POST['email'];
    $prefectures_id = $_POST['prefectures_id'];
    $age = (!empty($_POST['age']))? $_POST['age']: '0';
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    $pic = (empty($_FILES['pic']['name']) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
    
    //必須項目の未入力チェック
    validRequired($name, 'name');
    validRequired($email, 'email');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        if($name != $dbFormData['name']){
            validMaxLen($name, 'name');
        }
        
        if($email != $dbFormData['email']){
            validMaxLen($email, 'email');
            validEmail($email, 'email');
        }

        if($age != $dbFormData['age']){
            validHalfNum($age, 'age');
        }
        

        if(empty($err_msg)){
            debug('バリデーションOK;');
            try{
                debug('ユーザ更新処理を開始します。');
                //DB接続
                $dbh = dbConnect();
                //SQL文作成
                $sql = 'UPDATE users SET name = :name, email = :email, prefectures_id = :prefectures_id, age = :age, pic = :pic WHERE id = :u_id AND delete_flg = 0';
                $data = array(":name" => $name, ":email" => $email, ":prefectures_id" => $prefectures_id, ":age" => $age, ":pic" => $pic, ":u_id" => $u_id);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
        
                if($stmt){
                    debug('ユーザ情報を更新しました。');
                    $_SESSION['succes_msg'] = SUC01;

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


?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>
<body>
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <h2>プロフィール編集</h2>
        
        <div class="form">
            <form method="post" enctype="multipart/form-data">
                <p class="caution"><span class="required-mark">※</span>は必須入力項目です。</p>
                <div class="msg-area">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label>
                    <div class="dropIcon-area">
                        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                        <input class="img-file" type="file" name="pic">
                        <img class="icon-img" src="<?php echo (!empty($dbFormData['pic']))? $dbFormData['pic'] : 'image/初期アイコン.png' ?> " alt="">
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
                                <option value="<?php echo $val['id'] ?>" <?php if(getFormData('prefectures_id') === $val['id']) echo 'selected' ?>><?php echo $val['name'] ?></option>
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
                    <input type="submit" value="更新" class="btn regist-btn">
                </label>
            </form>
        </div>
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>