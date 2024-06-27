<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // パスワードのハッシュ化

    // 性別を数値にマッピング
    $sex_map = [
        'male' => 1,
        'female' => 2,
        'no_answer' => 3
    ];
    $sex = $sex_map[$sex];


    // タイムスタンプを取得
    $timestamp = date('Y-m-d H:i:s');

    // データベースに接続
    $servername = "db.sakura.ne.jp";
    $username_db = "gs1";  // データベースユーザー名
    $password_db = "--";  // データベースパスワード
    $dbname = "gs1_kadai_php01";
    // $port = 3306; // デフォルトのMySQLポート番号

 
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

  // ユーザー情報をデータベースに保存
  $stmt = $conn->prepare("INSERT INTO users (username, password, sex, age, timestamp) VALUES (?, ?, ?, ?, ?)");
  if ($stmt === false) {
      die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
  }
  if ($stmt->bind_param("ssiis", $username, $hashed_password, $sex, $age, $timestamp) === false) {
      die("Bind param failed: (" . $stmt->errno . ") " . $stmt->error);
  }
  if ($stmt->execute() === false) {
      die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
  } else {
      // ユーザー登録が成功した場合、バッファをクリアしてからリダイレクト
      ob_start();
      header("Location:login.php");
      ob_end_flush();
      exit();
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Register</h1>
        </div>
    </header>
    <div class="container">
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required><br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>
            <label for="sex">Sex:</label>
            <select name="sex" id="sex" required>
                <option value="male">男性</option>
                <option value="female">女性</option>
                <option value="no_answer">無回答</option>
            </select><br>
            <label for="age">Age:</label>
            <select name="age" id="age" required>
                <option value="10">10代</option>
                <option value="20">20代</option>
                <option value="30">30代</option>
                <option value="40">40代</option>
                <option value="50">50代</option>
                <option value="60">60代</option>
                <option value="70">70代</option>
                <option value="80">80代</option>
                <option value="90">90代</option>
            </select><br>
            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>
