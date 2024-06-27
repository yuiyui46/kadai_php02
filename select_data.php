 <?php
        session_start();
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: login.php');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $dataTitle = $_POST['dataTitle'];
            $year = $_POST['year'];
            $username = $_SESSION['username'];
        
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $csvFile = $_FILES['file']['tmp_name'];
                $csvFilePath = 'uploads/' . basename($_FILES['file']['name']);
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                move_uploaded_file($csvFile, $csvFilePath);
                $_SESSION['csvFilePath'] = $csvFilePath;


                 // タイムスタンプを取得
                 $timestamp = date('Y-m-d H:i:s');           

                 // データベースに接続
                 $servername = "db.sakura.ne.jp";
                 $username_db = "gs1";  // XAMPPデフォルトのユーザー名
                 $password_db = "--";  // XAMPPデフォルトのパスワード
                 $dbname = "gs1_kadai_php01";
                 // $port = 3310; // 新しいポート番号
        
                $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        
                // 接続エラーチェック
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
        
                // ユーザーデータを保存
                $stmt = $conn->prepare("INSERT INTO user_data (username, data_title, year, csv_file_path, timestamp) VALUES (?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                if ($stmt->bind_param("ssiss", $username, $dataTitle, $year, $csvFilePath, $timestamp) === false) {
                    die("Bind param failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                if ($stmt->execute() === false) {
                    die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                }
        
                $user_data_id = $stmt->insert_id;
                $stmt->close();
        
                $_SESSION['user_data_id'] = $user_data_id;
                $_SESSION['dataTitle'] = $dataTitle;
                $_SESSION['year'] = $year;
        
                header('Location: analyze_data.php');
                exit();
            }
        }
        ?>        

<!DOCTYPE html>
<html>
<head>
    <title>Select Data</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Select Data</h1>
        </div>
    </header>
    <div class="container">
        <form method="post" action="" enctype="multipart/form-data">
            <label for="dataTitle">Data Title:</label>
            <input type="text" name="dataTitle" id="dataTitle" required><br>
            <label for="year">Year:</label>
            <select name="year" id="year" required>
                <?php
                for ($i = 1980; $i <= 2024; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select><br>
            <label for="file">Select CSV file:</label>
            <input type="file" name="file" id="file" accept=".csv" required><br>
            <input type="submit" value="Upload">
        </form>
        <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
    </div>
</body>
</html>
