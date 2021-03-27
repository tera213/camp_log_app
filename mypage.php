<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('マイページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

//ユーザIDを取得
$u_id = $_SESSION['user_id'];

//ページ番号
$p_num = (!empty($_GET['p_num']))? $_GET['p_num'] : 1;
debug('ページ番号：'.$p_num);

//先頭ページ
$pageFirstNum = (($p_num - 1) * 9) + 1;
//都道府県データ取得
$dbPrefucData = getPrefectures();
//登録しているレビュー情報を取得
$dbReviewData = getReviewMyList($p_num, $u_id);



?>




<?php
$siteTitle = 'マイページ';
require('head.php');
?>

<body class="colum-2">
    
    <!-- ヘッダー -->
    <?php require('header.php') ?>
    <div id="js-slideMsg" class="slideMsg" style="display:none">
        <p>
        <?php echo getSessionFlash('succes_msg') ?>
        </p>
        
    </div>


    <!-- メイン -->
    <main>
        
        <h2>マイページ</h2>
        <!-- サイドバー -->
        <div class="content">
            <?php require('sidebar.php') ?>
            <div class="main">
                <h3>登録したレビュー</h3>
                <div class="main-content">
                    <?php 
                        foreach($dbReviewData['review_list'] as $key => $val){
                    ?>
                        <a href="reviewDetail.php?r_id=<?php echo $val['id'] ?>" class="revContent">
                            <img src="<?php echo (!empty($val['pic1'])) ? $val['pic1'] : 'image/sample-img.png' ?>" alt="">
                            <div class="revText">
                                <span><?php echo $val['prefec_name'] ?></span>
                                <p class="site-name"><?php echo $val['name'] ?></p>
                                <p class="price">¥<?php echo number_format($val['price']) ?></p>
                                <p class="eval">評価：<?php echo $val['eval_ave'] ?></p>
                            </div>
                        </a>
                    <?php
                        }
                    ?>
                </div>
                <div class="paging">
                <?php
                    //現在のページ
                    $currentPageNum = $p_num;
                    //トータルページ数
                    $totalPageNum = $dbReviewData['review_page'];
                    //ページング表示数
                    $pageColNum = 5;
                    //現在のページが総ページ数と同じかつ総ページ数が表示項目数以上なら左にリンク4個出す
                    if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
                        $minPageNum = $currentPageNum -4;
                        $maxPageNum = $currentPageNum;
                    //現在のページが、総ページ数の1ページ前なら、左にリンク3個、右に1個出す
                    }elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
                        $minPageNum = $currentPageNum - 3;
                        $maxPageNum = $currentPageNum + 1;
                    //現ページが2の場合は左にリンク1個、右にリンク3個出す。
                    }elseif($currentPageNum == 2 && $totalPageNum >= $pageColNum){
                        $minPageNum = $currentPageNum -1;
                        $maxPageNum = $currentPageNum +3;
                    //現ページが1の場合は左に何も出さない。みぎに5個出す。
                    }elseif($currentPageNum == 1 && $totalPageNum > $pageColNum){
                        $minPageNum = $currentPageNum;
                        $maxPageNum = 5;
                    //総ページ数が表示項目数より少ない場合は総ページ数をMAX、ループのMinを1に設定
                    }elseif($totalPageNum < $pageColNum){
                        $minPageNum = 1;
                        $maxPageNum = $totalPageNum;
                    }else{
                        $minPageNum = $currentPageNum - 2;
                        $maxPageNum = $currentPageNum + 2;
                    }
                ?>
                    <ul>
                    <?php
                        if($currentPageNum != 1){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.($currentPageNum - 1) : '?p_num='.($currentPageNum - 1) ?>" class="pageMove">前へ</a></li>
                    <?php
                        }
                        for($i = $minPageNum; $i <= $maxPageNum; $i++){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.$i : '?p_num='.$i ?>" class="pageNum <?php if($currentPageNum == $i) echo 'select' ?>"><?php echo $i ?></a></li>
                    <?php
                        }
                        if($currentPageNum !== $maxPageNum && $maxPageNum > 1){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.($currentPageNum + 1) : '?p_num='.($currentPageNum + 1) ?>" class="pageMove">次へ</a></li>
                    <?php
                        }
                    ?>
                    </ul>
                    <div class="list_number">
                        <p><?php echo $pageFirstNum.'-'.($pageFirstNum + 9)?>/<?php echo $dbReviewData['review_all'] ?>件 表示</p>
                    </div>
                </div>
            </div>
        </div>
        
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>