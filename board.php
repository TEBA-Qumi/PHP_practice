<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
    <style>
        form{
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h1>簡易掲示板</h1>
    
    <?php
        // DB接続設定
    	$dsn = 'データベース名';
    	$user = 'ユーザー名';
    	$password = 'パスワード';
    	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    	if($pdo != true){
    	    echo 'データベースへの接続に失敗しました。';
    	}
    // 	テーブルが存在しない場合、テーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS posts(
            id INT AUTO_INCREMENT PRIMARY KEY,
            name varchar(32),
            comment TEXT,
            date DATETIME,
            password varchar(100)
            )";
        $stmt = $pdo -> query($sql);
    
    // 入力フォームに値が入力された場合、新規投稿する
    if(!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['pass']) && empty($_POST['edit_num'])) {
        // フォームからのデータ取得
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $date = date("Y/m/d H:i:s");
        $pass =$_POST['pass'];
        
        // テーブルに値を追加
        $sql = "INSERT INTO posts (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
        $stmt = $pdo -> prepare($sql);
        $stmt->bindParam(':name', $name,PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment,PDO::PARAM_STR);
        $stmt->bindParam(':date', $date,PDO::PARAM_STR);
        $stmt->bindParam(':password', $pass,PDO::PARAM_STR);
        $stmt->execute();
        
        
    //削除フォームに値が入力されたら削除処理を行う
    } else if(!empty($_POST['delete']) && !empty($_POST['delete_pass'])) {
        //フォームから値を取得
        $delete = $_POST['delete'];
        $delete_pass = $_POST['delete_pass'];
        $id = $delete;
        //テーブルのパスワードを取得
        $sql = 'SELECT * FROM posts WHERE  id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
            foreach($results as $row) {
                $deletePass = $row['password'];
            }
        //パスワードを取得できているかチェック    
        // echo $deletePass;

        //上記で取得したパスワードと入力されたパスワードを比較
        if($delete_pass == $deletePass) {
        //比較した結果trueなら削除処理を行う
        	$sql = 'delete from posts where id=:id';
        	$stmt = $pdo->prepare($sql);
        	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
        	$stmt->execute();
        }else{
            //比較した結果falseなら警告
            echo 'パスワードが間違っています';
        }
    //編集フォームに値が入力されたら編集処理を行う
    }else if(!empty($_POST['edit']) && !empty($_POST['edit_pass'])) {
        //フォームに入力した値を取得
        $edit = $_POST['edit'];
        $edit_pass = $_POST['edit_pass'];
        $id = $edit;
        //テーブルに格納されたパスワードを取得
        $sql = 'SELECT * FROM posts WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
            foreach($results as $row) {
                $editPassword = $row['password'];
                //取得したパスワードと入力したパスワードを比較した結果、trueならテーブルの各値を取得
                if($edit_pass == $editPassword) {
                    $edit_num = $row['id'];
                    $edit_name = $row['name'];
                    $edit_comment = $row['comment'];
                    $edit_password = $editPassword;
                }else{
                    //比較した結果、falseなら警告
                    echo "パスワードが間違っています!";
                }
            }
            //テーブルからパスワードを取得できたかチェック
            // echo $edit_password;
    }
    
    //編集フォームでの処理で取得した値が入力されているなら編集して投稿する
    if(!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['edit_num'])) {
        //編集した値を取得
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $id = $_POST['edit_num'];
        $date = date("Y/m/d H:i:s");
        $pass = $_POST['pass'];
        
        //編集した値をDBに書き込む
        $sql = 'UPDATE posts SET name=:name,comment=:comment,date=:date,password=:password WHERE id=:id';
        $stmt = $pdo->prepare($sql);
    	$stmt->bindParam(':name', $name, PDO::PARAM_STR);
    	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
    	$stmt->bindParam(':date', $date, PDO::PARAM_STR);
    	$stmt->bindParam(':password', $pass, PDO::PARAM_STR);
    	$stmt->execute();
    }
    
    ?>
    
    <!--入力フォーム-->
    <form action="" method="post" name="write">
        <!--phpで書かれた部分で編集機能で取得した値をフォームに取得する-->
        <input type="text" name="name" placeholder="名前" 
        value="<?php if(isset($edit_name)){echo $edit_name;} ?>" required><br>
        <input type="text" name="comment" placeholder="コメント"  size="50" value="<?php if(isset($edit_comment)){echo $edit_comment;} ?>" required>
        <input type="hidden" name="edit_num" value="<?php if(isset($edit_num)){echo $edit_num;} ?>">
        <br>
        <input type ="password" name ="pass" placeholder ="パスワード"  value="<?php if(isset($edit_password)){echo $edit_password;} ?>" required>
        <input type="submit" name="submit">    
    </form>

    <!--削除フォーム-->
    <form action="" method="post">
        <input type="number" name="delete" placeholder="削除対象番号">
        <br>
        <input type ="password" name ="delete_pass" placeholder ="パスワード">
        <input type="submit" name="submit2" value="削除">
    </form>
    
    <!--編集フォーム-->
    <form action="" method="post">
        <input type="number" name="edit" placeholder="編集対象番号">
        <br>
        <input type ="password" name ="edit_pass" placeholder ="パスワード">
        <input type="submit" name="submit3" value="編集">
    </form>
    <?php
    //レコード毎に各カラムの値を表示
    $sql = 'SELECT * FROM posts';
    $stmt = $pdo -> query($sql);
    $results = $stmt->fetchAll();
    foreach($results as $row) {
        echo '投稿番号:'.$row['id'].'<br>';
        echo '名前:'.$row['name'].'<br>';
        echo 'コメント:'.$row['comment'].'<br>';
        echo '日付:'.$row['date'].'<br>';
        echo '<hr>';
    }
    ?>
    
</body>
</html>