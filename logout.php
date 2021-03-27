<?php

//共通変数・関数ファイルの読み込み
require('function.php');

debug('********************************************');
debug('ログアウト');
debug('********************************************');
debugLogStart();

//認証ファイル
require('auth.php');

debug('セッションを削除します。');
session_destroy();

header("Location:index.php");