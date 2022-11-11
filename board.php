<?php

session_start();
mb_internal_encoding("utf8");

// 1.ログインしていなければ、ログインページにリダイレクト
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
}

//変数の初期化
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {   //アクセスの仕方がPOST通信かGET通信か判別している。
    //POST処理
    //エスケープ処理　postを使う決まり文句☟　☟form内の<input..name="〇〇">の箱の名前
    $input["title"] = htmlentities($_POST["title"] ?? "", ENT_QUOTES);       //悪意のあるscriptなどを処理する、XSSという。
    $input["comments"] = htmlentities($_POST["comments"] ?? "", ENT_QUOTES);
    //バリデーションチェック
    //1.タイトルのバリデーション

    if (strlen(trim($input["title"] ?? "")) == 0) { //入力されているかの確認
        $errors["title"] = "タイトルを入力してください";
    }

    //4.コメントのバリデーション

    if (strlen(trim($input["comments"] ?? "")) == 0) {  //入力されているかの確認
        $errors["comments"] = "コメントを入力してください";
    }

    //※☝emptyとはある変数が空かどうかチェックするもの。
    //   if(empty (変数)) {処理内容;}

    if (empty($errors)) {
        try {  //※try catchは、例外処理に使用される手法。
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", ""); //DBに接続　(sample7_89に詳しく)
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーモードを「警告」に設定
            //☝これでエラーモードの設定      ☝エラーの種類
            $stmt = $pdo->prepare(" INSERT INTO post(user_id,title,comments) VALUES(?,?,?) ");
            $stmt->execute(array($_SESSION["id"], $input["title"], $input["comments"]));
            //DB切断
            $pdo = NULL;
        } catch (PDOException $e) {  //catchの例外を記述する部分にはPDOE...を記述し隣に【$e】を記述。ここには何らかの変数を記述する必要がある変数名はなんでもいい。
            $e->getMessage(); // 例外発生時にエラーメッセージを出力
        }
    }
}


// 4.GETアクセス時の処理（DBに接続し、投稿内容を取り出す）
try {
    $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", ""); // DBに接続
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // エラーモードを「警告」に設定
    $posts = $pdo->query(" SELECT title,comments,name,posted_at FROM post INNER JOIN user ON post.user_id = user.id ORDER BY posted_at DESC");
    $pdo = NULL; // DB切断
} catch (PDOException $e) {
    $e->getMessage(); // 例外発生時にエラーメッセージを出力
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>keijiban</title>
    <link rel="stylesheet" type="text/css" href="board.css">
</head>

<body>
    <div class="logo">
        <img src="4eachblog_logo.jpg">
        <div class="logout">
            <p>こんにちは<?php echo $_SESSION["name"] ?> さん</p>
            <a href="logout.php">ログアウト</a>
        </div>
    </div>


    <div class="top">
        <ul>
            <li>トップ</li>
            <li>プロフィール</li>
            <li>4eachについて</li>
            <li>登録フォーム</li>
            <li>問い合わせ</li>
            <li>その他</li>
        </ul>
    </div>

    <main>
        <div class="main-container">
            <div class="left">
                <h2>プログラミングに役立つ掲示板</h2>
                <form method="POST" action="">
                    <div class="keijiban">
                        <h3>入力フォーム</h3>
                        <div class="item">
                            <p><label>タイトル</label></p>
                            <input type="text" class="text" name="title" size="35">
                            <?php if (!empty($errors)) : ?>
                                <p class="err_message"><?php echo $errors["title"] ?? '';?></P>
                            <?php endif; ?>
                        </div>
                        <div class="item">
                            <p><lavel>コメント</lavel></p>
                            <textarea name="comments" rows="4" cols="40"></textarea>
                            <?php if (!empty($errors)) : ?>
                                <p class="err_message"><?php echo $errors["comments"] ?? '';?></p>
                            <?php endif; ?>
                        </div>
                        <div class="item">
                            <input type="submit" name="submit" value="送信する">
                        </div>
                    </div>
                </form>
                <?php foreach ($posts as $post) : ?>
                    <div class='kiji'>
                        <h3><?php echo $post["title"] ?></h3>
                        <div class='contents'><?php echo $post["comments"] ?></div>
                        <div class='handlename'>投稿者：<?php echo $post["name"]; ?></div>
                        <div class='time'>投稿時間：
                            <?php
                            // 日付のフォーマットの変更
                            $posted_at = new DateTime($post["posted_at"]);
                            echo $posted_at->format('Y年m月d日 H:i');
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="right">
                <h3>人気の記事</h3>
                <ul>
                    <li>PHPのオススメ本</li>
                    <li>PHPMyAdminの使い方</li>
                    <li>今人気のエディタTop5</li>
                    <li>HTMLの基礎</li>
                </ul>
                <h3>オススメリンク</h3>
                <ul>
                    <li>インターノース株式会社</li>
                    <li>XAMPPのダウンロード</li>
                    <li>Eclipseのダウンロード</li>
                    <li>Bracketsのダウンロード</li>
                </ul>
                <h3>カテゴリ</h3>
                <ul>
                    <li>HTML</li>
                    <li>PHP</li>
                    <li>MySQL</li>
                    <li>JavaScript</li>
                </ul>
            </div>



        </div>
    </main>
    <footer>copyright © internous | 4each blog the which provides A to Z about programming.</footer>
</body>

</html>