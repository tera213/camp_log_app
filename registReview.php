<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('レビュー登録ページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

//ユーザID
$u_id = '';
//レビューID
$r_id = '';
//レビュー情報
$dbFormData = '';
//都道府県情報
$dbPrefucData = '';
//登録ページが編集ページかのフラグ
$page_flg = true; //初期値は登録ページ


//ユーザIDを取得
$u_id = $_SESSION['user_id'];
//都道府県データ取得
$dbPrefucData = getPrefectures();

//編集画面の場合は登録されているレビュー情報を取得
if(!empty($_GET['r_id'])){
    //レビューID取得
    $r_id = $_GET['r_id'];
    //レビュー情報を取得
    $dbFormData = getReviewOne($r_id);
    debug('レビュー情報：'.print_r($dbFormData, true));

    //GETパラメータの不正入力チェック
    //入力されたレビューIDの情報を登録したユーザIDが正しいかチェック
    if($dbFormData['user_id'] !== $u_id){
        debug('不正な値が入力されました。');
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
    }

    $page_flg = false; //フラグを編集ページに
}


//post送信がある場合
if(!empty($_POST)){
    debug('POST送信がありました。');
    debug('POST：'.print_r($_POST, true));
    debug('FILES：'.print_r($_FILES, true));

    //削除の場合
    if(!empty($_POST['del'])){
        debug('削除が選択されました。');

        try{
            //db接続
            $dbh = dbConnect();
            //sql文作成
            $sql = 'UPDATE campsite SET delete_flg = 1 WHERE user_id = :u_id AND id = :r_id AND delete_flg = 0';
            $data = array(":u_id" => $u_id, ":r_id" => $r_id);
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            if($stmt){
                $_SESSION['succes_msg'] = SUC03;
                debug('削除しました。');
                debug('マイページへ遷移します。');
                header("Location:mypage.php");
                exit();
            }
        }catch(Exception $e){
            error_log('エラー発生：'.$e -> getMessage());
            $err_msg['common'] = MSG07;
        }
    }

    $name = $_POST['name'];
    $prefectures_id = $_POST['prefectures_id'];
    $address = $_POST['address'];
    $site_name = $_POST['site_name'];
    $price = $_POST['price'];
    $comment = $_POST['comment'];
    $eval_site = $_POST['eval_site'];
    $eval_bath = $_POST['eval_bath'];
    $eval_cook = $_POST['eval_cook'];
    $eval_shop = $_POST['eval_shop'];
    $eval_location = $_POST['eval_location'];
    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
    $pic1 = (empty($_FILES['pic1']['name']) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
    $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
    $pic2 = (empty($_FILES['pic2']['name']) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
    $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
    $pic3 = (empty($_FILES['pic3']['name']) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;
    //評価点の平均値
    $eval_ave = (($eval_site + $eval_bath + $eval_cook + $eval_shop + $eval_location) / 5); 
    debug('評価平均：'.$eval_ave);


    //未入力チェック
    validRequired($name, 'name');
    validRequired($prefectures_id, 'prefectures_id');
    validRequired($site_name, 'site_name');
    validRequired($price, 'price');
    validRequired($eval_site, 'eval_site');
    validRequired($eval_bath, 'eval_bath');
    validRequired($eval_cook, 'eval_cook');
    validRequired($eval_shop, 'eval_shop');

    if(empty($err_msg)){
        debug('未入力チェックOK');
        if($page_flg){
            //登録ページの場合
            validMaxLen($name, 'name');

            validHalfNum($prefectures_id, 'prefectures_id');

            validMaxLen($address, 'address');

            validMaxLen($site_name, 'site_name');

            validHalfNum($price, 'price');

            validMaxLen($comment, 'comment');

            validSelectEval($eval_site, 'eval_site');
            validSelectEval($eval_bath, 'eval_bath');
            validSelectEval($eval_cook, 'eval_cook');
            validSelectEval($eval_shop, 'eval_shop');
            
        }else{
            //編集ページの場合
            if($name != $dbFormData['name']){
                validMaxLen($name, 'name');
            }
            if($prefectures_id != $dbFormData['prefectures_id']){
                validHalfNum($prefectures_id, 'prefectures_id');
            }
            if($address != $dbFormData['address']){
                validMaxLen($address, 'address');
            }
            if($site_name != $dbFormData['site_name']){
                validMaxLen($site_name, 'site_name');
            }
            if($price != $dbFormData['price']){
                validHalfNum($price, 'price');
            }
            if($comment != $dbFormData['comment']){
                validMaxLen($comment, 'comment');
            }
            if($eval_site != $dbFormData['eval_site']){
                validSelectEval($eval_site, 'eval_site');
            }
            if($eval_bath != $dbFormData['eval_bath']){
                validSelectEval($eval_bath, 'eval_bath');
            }
            if($eval_cook != $dbFormData['eval_cook']){
                validSelectEval($eval_cook, 'eval_cook');
            }
            if($eval_shop != $dbFormData['eval_shop']){
                validSelectEval($eval_shop, 'eval_shop');
            }
        }
        if(empty($err_msg)){
            debug('バリデーションOK');

            try{
                //DB接続
                $dbh = dbConnect();
                if($page_flg){
                    //登録の場合
                    $sql = 'INSERT INTO campsite (name, user_id, prefectures_id, address, site_name, price, pic1, pic2, pic3, eval_site, eval_bath, eval_cook, eval_shop, eval_location, eval_ave, comment, create_date) 
                    VALUES(:name, :user_id, :prefectures_id, :address, :site_name, :price, :pic1, :pic2, :pic3, :eval_site, :eval_bath, :eval_cook, :eval_shop, :eval_location, :eval_ave, :comment, :date)';
                    $data = array(":name" => $name, ":user_id" => $u_id, ":prefectures_id" => $prefectures_id, ":address" => $address, ":site_name" => $site_name, ":price" => $price, ":pic1" => $pic1, ":pic2" => $pic2, ":pic3" => $pic3, ":eval_site" => $eval_site, ":eval_bath" => $eval_bath, ":eval_cook" => $eval_cook, ":eval_shop" => $eval_shop, ":eval_location" => $eval_location, ":eval_ave" => $eval_ave, "comment" => $comment, ":date" => date('Y-m-d H:i:s'));
                }else{
                    //編集の場合
                    $sql = 'UPDATE campsite SET name = :name, user_id = :user_id, prefectures_id = :prefectures_id, address = :address, site_name = :site_name, price = :price, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, eval_site = :eval_site, eval_bath = :eval_bath, eval_cook = :eval_cook, eval_shop = :eval_shop, eval_location = :eval_location, eval_ave = :eval_ave, comment = :comment WHERE id = :r_id AND delete_flg = 0';
                    $data = array(":name" => $name, ":user_id" => $u_id, ":prefectures_id" => $prefectures_id, ":address" => $address, ":site_name" => $site_name, ":price" => $price, ":pic1" => $pic1, ":pic2" => $pic2, ":pic3" => $pic3, ":eval_site" => $eval_site, ":eval_bath" => $eval_bath, ":eval_cook" => $eval_cook, ":eval_shop" => $eval_shop, ":eval_location" => $eval_location, ":eval_ave" => $eval_ave, "comment" => $comment, "r_id" => $r_id);
                }
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                if($stmt){
                    if($page_flg){
                        $_SESSION['succes_msg'] = SUC02;
                    }else{
                        $_SESSION['succes_msg'] = SUC01;
                    }
                    
                    debug('マイページへ遷移します。');
                    header("Location:mypage.php");
                }
            }catch(Exception $e){
                error_log('エラー発生：'.$e -> getMessage());
                $err_msg['common'] = MSG07;
            }
        }

    }

    

}







?>



<?php
$siteTitle = 'レビュー登録';
require('head.php');
?>

<body class="">
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <h2><?php echo ($page_flg)? 'レビュー登録' : 'レビュー編集'; ?></h2>
        
            <form method="post" enctype="multipart/form-data">
                <p class="caution regist-caution"><span class="required-mark">※</span>は必須入力項目です。</p>
                <div class="msg-area">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <div class="main-container">
                    <div class="left">
                        <label>
                            <p>キャンプ場名<span class="required-mark">※</span></p>
                            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
                        </label>
                        <div class="msg-area">
                            <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
                        </div>
                        <label>
                            <p>住所</p>
                            <select name="prefectures_id">
                            <option value="0" selected>都道府県</option>
                                <?php
                                    foreach($dbPrefucData as $key => $val){
                                ?>
                                        <option value="<?php echo $val['id'] ?>" 
                                        <?php 
                                            if(!empty($dbFormData['prefectures_id'])){
                                                if($val['id'] == $dbFormData['prefectures_id']) echo 'selected';
                                            }  
                                        ?>
                                        ><?php echo $val['name'] ?>
                                        </option>
                                <?php
                                    }
                                ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['prefectures_id'])) echo $err_msg['prefectures_id']; ?>
                            </div>
                            <input type="text" name="address" placeholder="市区町村番地" value="<?php echo getFormData('address'); ?>">
                            <div class="msg-area">
                                <?php if(!empty($err_msg['address'])) echo $err_msg['address']; ?>
                            </div>
                        </label>
                        <label>
                            <p>区画名<span class="required-mark">※</span></p>
                            <input type="text" name="site_name" value="<?php echo getFormData('site_name'); ?>">
                        </label>
                        <div class="msg-area">
                            <?php if(!empty($err_msg['site_name'])) echo $err_msg['site_name']; ?>
                        </div>
                        <label>
                            <p>金額<span class="required-mark">※</span></p>
                            <input type="number" name="price" value="<?php echo getFormData('price'); ?>">
                        </label>
                        <div class="msg-area">
                            <?php if(!empty($err_msg['site_name'])) echo $err_msg['site_name']; ?>
                        </div>
                    </div>
                    <div class="right">
                        <p>評価（5段階）<span class="required-mark">※</span></p>
                        <div class="eval">
                            <p>区画：</p>
                            <select name="eval_site">
                            <?php
                                for($i = 1; $i < 6; $i++){
                            ?>
                                    <option value="<?php echo $i ?>"
                                    <?php 
                                        if(!$page_flg){
                                            if($i == $dbFormData['eval_site']) echo 'selected';
                                        }
                                    ?>
                                    ><?php echo $i ?></option>
                            <?php
                                }
                            ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['eval_site'])) echo $err_msg['eval_site']; ?>
                            </div>
                        </div>
                        
                        <div class="eval">
                            <p>トイレ・シャワー：</p>
                            <select name="eval_bath">
                            <?php
                                for($i = 1; $i < 6; $i++){
                            ?>
                                    <option value="<?php echo $i ?>"
                                    <?php 
                                        if(!$page_flg){
                                            if($i == $dbFormData['eval_bath']) echo 'selected';
                                        }
                                    ?>
                                    ><?php echo $i ?></option>
                            <?php
                                }
                            ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['eval_bath'])) echo $err_msg['eval_bath']; ?>
                            </div>
                        </div>
                        
                        <div class="eval">
                            <p>炊事場：</p>
                            <select name="eval_cook">
                            <?php
                                for($i = 1; $i < 6; $i++){
                            ?>
                                    <option value="<?php echo $i ?>"
                                    <?php 
                                        if(!$page_flg){
                                            if($i == $dbFormData['eval_cook']) echo 'selected';
                                        }
                                    ?>
                                    ><?php echo $i ?></option>
                            <?php
                                }
                            ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['eval_cook'])) echo $err_msg['eval_cook']; ?>
                            </div>
                        </div>
                        
                        <div class="eval">
                            <p>売店・管理棟：</p>
                            <select name="eval_shop">
                            <?php
                                for($i = 1; $i < 6; $i++){
                            ?>
                                    <option value="<?php echo $i ?>"
                                    <?php 
                                        if(!$page_flg){
                                            if($i == $dbFormData['eval_shop']) echo 'selected';
                                        }
                                    ?>
                                    ><?php echo $i ?></option>
                            <?php
                                }
                            ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['eval_shop'])) echo $err_msg['eval_shop']; ?>
                            </div>
                        </div>
                        
                        <div class="eval">
                            <p>立地：</p>
                            <select name="eval_location">
                            <?php
                                for($i = 1; $i < 6; $i++){
                            ?>
                                    <option value="<?php echo $i ?>"
                                    <?php 
                                        if(!$page_flg){
                                            if($i == $dbFormData['eval_location']) echo 'selected';
                                        }
                                    ?>
                                    ><?php echo $i ?></option>
                            <?php
                                }
                            ?>
                            </select>
                            <div class="msg-area">
                                <?php if(!empty($err_msg['eval_location'])) echo $err_msg['eval_location']; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="comment-container">
                    <label>
                        <p>コメント</p>
                        <textarea id="js-comment" name="comment" id="" cols="30" rows="10"><?php echo getFormData('comment'); ?></textarea>
                        
                    </label>
                    <p id="js-textCount"><span>0</span>/255文字以内</p>
                    <div class="msg-area">
                        <?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
                    </div>
                </div>

                <div class="img-container">
                    <label>
                        <div class="dropIcon-area">
                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                            <input class="img-file" type="file" name="pic1">
                            <img class="site-img" src="<?php if(!empty($dbFormData['pic1'])) echo $dbFormData['pic1'] ?>" <?php if(!empty($dbFormData['pic1'])) echo 'style="display:inline"' ?> alt="">
                            <p>画像をドロップ&ドラッグ</p>
                        </div>
                    </label>
                    <label>
                        <div class="dropIcon-area">
                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                            <input class="img-file" type="file" name="pic2">
                            <img class="site-img" src="<?php if(!empty($dbFormData['pic2'])) echo $dbFormData['pic2'] ?>" <?php if(!empty($dbFormData['pic2'])) echo 'style="display:inline"' ?> alt="">
                            <p>画像をドロップ&ドラッグ</p>
                        </div>
                    </label>
                    <label>
                        <div class="dropIcon-area">
                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                            <input class="img-file" type="file" name="pic3">
                            <img class="site-img" src="<?php if(!empty($dbFormData['pic3'])) echo $dbFormData['pic3'] ?>" <?php if(!empty($dbFormData['pic3'])) echo 'style="display:inline"' ?> alt="">
                            <p>画像をドロップ&ドラッグ</p>
                        </div>
                    </label>
                </div>
                
                
                <label>
                    <input type="submit" value="<?php echo ($page_flg)? '登録' : '更新'; ?>" class="btn regist-btn">
                </label>
                <label>
                    <input name="del" type="submit" value="削除" class="btn regist-btn" style="<?php echo ($page_flg)? 'display:none;' : 'margin-top:20px;'; ?>">
                </label>
            </form>
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>