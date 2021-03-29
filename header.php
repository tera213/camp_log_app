<header id="header">
        <div class="logo">
            <h1>
                <a href="top.php">
                    <img src="image/camp_logo2.png" alt="ロゴ">
                </a>
                
            </h1>
        </div>
        <?php
            if(!empty($_SESSION['user_id'])){
        ?>
                <nav>
                    <ul>
                        <li><a href="logout.php">ログアウト</a></li>
                        <li><a href="mypage.php">マイページ</a></li>
                    </ul>
                </nav>
        <?php
            }else{
        ?>
                <nav>
                    <ul>
                        <li><a href="index.php">ログイン</a></li>
                        <li><a href="registUser.php">新規登録</a></li>
                    </ul>
                </nav>
        <?php
            }
        ?>

    </div>
</header>