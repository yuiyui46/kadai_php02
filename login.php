<?php
                session_start();
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $username = $_POST['username'];
                    $password = $_POST['password'];

                // タイムスタンプを取得
                $timestamp = date('Y-m-d H:i:s');
                            
                // データベースに接続
                $servername = "db.sakura.ne.jp";
                $username_db = "gs1";  // XAMPPデフォルトのユーザー名
                $password_db = "--";  // XAMPPデフォルトのパスワード
                $dbname = "gs1_kadai_php01";
                // $port = 3310; // 新しいポート番号
                
                    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
                
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                
                    // ユーザー情報をデータベースから取得
                    $stmt = $conn->prepare("SELECT password, sex, age FROM users WHERE username = ?");
                    if ($stmt === false) {
                        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                    }
                
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $stmt->store_result();
                
                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($hashed_password, $sex, $age);
                        $stmt->fetch();
                        if (password_verify($password, $hashed_password)) {
                
                            // ユーザーが認証された場合、user_loginテーブルにデータを格納
                            $stmt_insert = $conn->prepare("INSERT INTO user_login (username, password, sex, age, timestamp) VALUES (?, ?, ?, ?, ?)");
                            if ($stmt_insert === false) {
                                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                            }
                            if ($stmt_insert->bind_param("sssis", $username, $hashed_password, $sex, $age, $timestamp) === false) {
                                die("Bind param failed: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
                            }
                            if ($stmt_insert->execute() === false) {
                                die("Execute failed: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
                            }
                
                            $_SESSION['loggedin'] = true;
                            $_SESSION['username'] = $username;
                            $_SESSION['sex'] = $sex;
                            $_SESSION['age'] = $age;
                            header('Location: select_data.php');
                            exit();
                        } else {
                            $error = "Invalid username or password";
                        }
                    } else {
                        $error = "Invalid username or password";
                    }
                
                    $stmt->close();
                    $conn->close();
                }
                ?>
                
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login</title>
            <link rel="stylesheet" type="text/css" href="css/styles.css">
        </head>
        <body>
            <header>
                <div class="container">
                    <h1>Login</h1>
                </div>
            </header>
            <div class="container">
                <form method="post" action="">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required><br>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required><br>
                    <input type="submit" value="Login">
                </form>
                <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
            </div>
        </body>
        </html>