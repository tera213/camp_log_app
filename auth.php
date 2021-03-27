<?php

//ログイン済ユーザかチェック
if(!empty($_SESSION['user_id'])){
    debug('ログイン済ユーザです。');

    //ログイン有効期限のチェック
    if( ($_SESSION['login_time'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限が切れています。');
        if($_SERVER['PHP_SELF'] != 'index.php'){ //ログインページ以外はログインページに遷移
            //セッション削除
            session_destroy();
            debug('ログインページに遷移します。');
            header("Location:index.php");
        }
    }else{
        debug('ログイン有効期限内です。');
        //ログイン時間を現在時間に更新
        $_SESSION['login_time'] = time();
        debug('ログイン有効期限：'. ($_SESSION['login_time'] + $_SESSION['login_limit']));
        if(basename($_SERVER['PHP_SELF']) == 'index.php'){ //ログインページのときはマイページへ遷移
            debug('マイページに遷移します。');
            header("Location:mypage.php");
        }
    }
}else{
    debug('未ログインユーザです。');
    if(basename($_SERVER['PHP_SELF']) != 'index.php'){ //ログインページ以外はログインページに遷移
        debug('ログインページに遷移します。');
        header("Location:index.php");
    }
}