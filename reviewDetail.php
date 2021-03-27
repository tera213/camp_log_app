<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('レビュー詳細ページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

//自分のユーザID取得
$u_id = $_SESSION['user_id'];



//レビューID取得
$r_id = $_GET['r_id'];

//ページ番号取得
$p_num = (!empty($_GET['p_num']))? $_GET['p_num'] : '';

//都道府県ID取得
$p_id = (!empty($_GET['p_id']))? $_GET['p_id'] : '';

//getパラメータ
$links = appendGetParam(array('r_id'));

//レビュー情報取得
$dbReviewData = getReviewOne($r_id);
debug('レビュー情報：'.print_r($dbReviewData, true));

//記録した人のユーザ情報取得
$dbRegistUserData = getUser($dbReviewData['user_id']);
debug('記録者：'.print_r($dbRegistUserData, true));






?>





<?php
$siteTitle = 'レビュー詳細';
require('head.php');
?>


<body class="colum-2">
    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メイン -->
    <main>
        <!-- サイドバー -->
        <div class="content">
            <?php require('sidebar.php') ?>
            <div class="main">
                <div class="main_img">
                    <img class="js-mainImg" src="<?php echo (!empty($dbReviewData['pic1']))? $dbReviewData['pic1'] : 'image/sample-img.png'; ?>" alt="">
                    <?php if($u_id == $dbReviewData['user_id']){
                    ?>
                        <div class="editBtn">
                            <a href="registReview.php?r_id=<?php echo $dbReviewData['id'] ?>">編集する</a>
                        </div>
                    <?php
                    }else{
                    ?>
                        <div class="registUser">
                            <p>記録した人</p>
                            <div class="iconImg">
                                <img src="<?php echo (!empty($dbRegistUserData['pic']))? $dbRegistUserData['pic'] : 'image/sample-img.png'; ?>" alt="">
                            </div>
                            <p class="name"><?php echo $dbRegistUserData['name'] ?></p>
                            <p><?php echo $dbRegistUserData['prefec_name'] ?></p>
                            <p></p>
                        </div>
                    <?php
                    }
                    ?>
                    
                </div>
                <div class="content_text">
                    <div class="text_left">
                        <p class="prefecture"><?php echo $dbReviewData['pref_name'] ?></p>
                        <p class="name"><?php echo $dbReviewData['name'] ?></p>
                        <p class="address">住所：<?php echo $dbReviewData['address'] ?></p>
                        <p class="site_name">区画名：<?php echo $dbReviewData['site_name'] ?></p>
                        <p class="price">¥<?php echo $dbReviewData['price'] ?></p>
                        <p class="comment">コメント：<?php echo $dbReviewData['comment'] ?></p>
                    </div>
                    <div class="text_right">
                        <ul>
                            <li>評価(5段階)</li>
                            <li>区画：<?php echo $dbReviewData['eval_site'] ?></li>
                            <li>トイレ・シャワー：<?php echo $dbReviewData['eval_bath'] ?></li>
                            <li>炊事場：<?php echo $dbReviewData['eval_cook'] ?></li>
                            <li>売店・管理棟：<?php echo $dbReviewData['eval_shop'] ?></li>
                            <li>立地：<?php echo $dbReviewData['eval_location'] ?></li>
                            <li>総合評価：<?php echo $dbReviewData['eval_ave'] ?></li>
                        </ul>
                    </div>
                </div>
                <div class="img_area">
                    <div class="img_container">
                        <img class="js-subImg" src="<?php echo (!empty($dbReviewData['pic1']))? $dbReviewData['pic1'] : 'image/sample-img.png'; ?>" alt="">
                    </div>
                    <div class="img_container">
                        <img class="js-subImg" src="<?php echo (!empty($dbReviewData['pic2']))? $dbReviewData['pic2'] : 'image/sample-img.png'; ?>" alt="">
                    </div>
                    <div class="img_container">
                        <img class="js-subImg" src="<?php echo (!empty($dbReviewData['pic3']))? $dbReviewData['pic3'] : 'image/sample-img.png'; ?>" alt="">
                    </div>
                </div>
                <a class="turnTop" href="top.php<?php echo $links ?>">一覧へ戻る</a>
            </div>
        </div>
        
        
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>