<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('トップページです。');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

//ユーザID
$u_id = '';
//レビュー情報
$dbFormData = '';
//都道府県情報
$dbPrefucData = '';
//都道府県ID
$p_id = (!empty($_GET['p_id']))? $_GET['p_id'] : 0;
debug('都道府県ID：'.$p_id);
//ページ番号
$p_num = (!empty($_GET['p_num']))? $_GET['p_num'] : 1;
debug('ページ番号：'.$p_num);
//ソート番号
$sort = (!empty($_GET['sort']))? $_GET['sort'] : 0;
//先頭ページ
$pageFirstNum = (($p_num - 1) * 9) + 1;

//getパラメータ
$links = appendGetParam(array('p_num'));
debug($links);


//ユーザIDを取得
$u_id = $_SESSION['user_id'];
//都道府県データ取得
$dbPrefucData = getPrefectures();
//レビューの表示用の情報取得
$dbReviewData = getReviewList($p_num, $p_id, $sort);

//getパラメータの不正入力チェック
if($p_num < 0 || ($p_num > $dbReviewData["review_page"] && $dbReviewData["review_page"] != 0)){
    debug('getパラメータに不正な値が入力されました。');
    debug('トップページへ遷移します。');
    header("Location:top.php");
}

?>





<?php
$siteTitle = 'トップページ';
require('head.php');
?>

<body class="colum-2">
    <!-- ヘッダー -->
    <?php require('header.php'); ?>


    <!-- メイン -->
    <main>
        
        <!-- サイドバー -->
        <div class="content">
            <div class="sidebar">
                <div class="serch-area">
                    <form method="get">
                        <p style="color:#fff">都道府県</p>
                        <select name="p_id">
                            <option value="0" selected></option>
                            <?php
                            foreach($dbPrefucData as $key => $val){
                            ?>
                                    <option value="<?php echo $val['id'] ?>" name="p_id"
                                    <?php 
                                        if(!empty($_GET['p_id'])){
                                            if($val['id'] == $_GET['p_id']) echo 'selected';
                                        }  
                                    ?>
                                    ><?php echo $val['name'] ?>
                                    </option>
                            <?php
                                }
                            ?>
                        </select>
                        <p style="color:#fff; padding-top:10px">並び順</p>
                        <select name="sort">
                                <option value="0"></option>
                                <option value="1" <?php if($sort == 1) echo 'selected' ?>>新しい順</option>
                                <option value="2" <?php if($sort == 2) echo 'selected' ?>>評価の高い順</option>
                                <option value="3" <?php if($sort == 3) echo 'selected' ?>>価格の安い順</option>
                                <option value="4" <?php if($sort == 4) echo 'selected' ?>>価格の高い順</option>
                        </select>
                        <input class="btn" type="submit" value="検索">
                    </form>
                </div>
            </div>
            <div class="main">
                <div class="main-content">
                    <?php
                        foreach($dbReviewData['review_list'] as $key => $val){
                    ?>
                            <a href="reviewDetail.php<?php echo (!empty($links))? $links.'&r_id='.$val['id'].'&p_num='.$p_num : '?r_id='.$val['id'].'&p_num='.$p_num?>" class="revContent">
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
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num=1' : '?p_num=1' ?>" class="pageMove">&lt;&lt;</a></li>
                    <?php
                        }
                    ?>
                    <?php
                        if($currentPageNum != 1){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.($currentPageNum - 1) : '?p_num='.($currentPageNum - 1) ?>" class="pageMove">&lt;</a></li>
                    <?php
                        }
                        for($i = $minPageNum; $i <= $maxPageNum; $i++){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.$i : '?p_num='.$i ?>" class="pageNum <?php if($currentPageNum == $i) echo 'select' ?>"><?php echo $i ?></a></li>
                    <?php
                        }
                        if($currentPageNum !== $maxPageNum && $maxPageNum > 1){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.($currentPageNum + 1) : '?p_num='.($currentPageNum + 1) ?>" class="pageMove">&gt;</a></li>
                    <?php
                        }if($currentPageNum !== $maxPageNum && $maxPageNum > 1){
                    ?>
                            <li><a href="<?php echo (!empty($links))? $links.'&p_num='.($totalPageNum) : '?p_num='.($totalPageNum) ?>" class="pageMove">&gt;&gt;</a></li>
                    <?php
                        }
                    ?>
                    </ul>
                    <div class="list_number">
                        <p><?php echo $pageFirstNum.'~'.($pageFirstNum + 9)?>/<?php echo $dbReviewData['review_all'] ?>件中</p>
                    </div>
                </div>
                
            </div>
        </div>
        
    </main>

    <!-- フッター -->
    <?php require('footer.php'); ?>

</body>
</html>