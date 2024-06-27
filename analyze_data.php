<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['dataTitle']) || !isset($_SESSION['year']) || !isset($_SESSION['csvFilePath'])) {
    die("Required session variables are missing.");
}

$dataTitle = $_SESSION['dataTitle'];
$year = $_SESSION['year'];
$csvFilePath = $_SESSION['csvFilePath'];

$api_key = 'dd'; // 正しいAPIキーを設定します
$statsDataId = '0002040991'; // 確認したデータIDを設定します

// e-stat APIからデータ取得
$endpoint = 'https://api.e-stat.go.jp/rest/3.0/app/json/getStatsData';
$params = [
    'appId' => $api_key,
    'statsDataId' => $statsDataId,
    'metaGetFlg' => 'Y',
    'cntGetFlg' => 'N',
    'explanationGetFlg' => 'N',
    'cdTime' => '15'  // 2022年のデータを指定する
];

$url = $endpoint . '?' . http_build_query($params);
$response = file_get_contents($url);
$data = json_decode($response, true);

// メタデータを取得
$metaEndpoint = 'https://api.e-stat.go.jp/rest/3.0/app/json/getMetaInfo';
$metaParams = [
    'appId' => $api_key,
    'statsDataId' => $statsDataId
];

$metaUrl = $metaEndpoint . '?' . http_build_query($metaParams);
$metaResponse = file_get_contents($metaUrl);
$metaData = json_decode($metaResponse, true);

// メタデータのデバッグ表示
if ($metaData === null || !isset($metaData['GET_META_INFO'])) {
    echo "<pre>";
    print_r($metaResponse);
    echo "</pre>";
    die("Error fetching meta data from e-stat API. Response: " . print_r($metaResponse, true));
}

// e-statデータをファイルに保存
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}
$estatFilePath = 'uploads/estat_data.json';
file_put_contents($estatFilePath, json_encode($data, JSON_PRETTY_PRINT));


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

// 実際のユーザーIDを設定
$user_data_id = 1; // ここに実際のユーザーIDを入力

// e-statデータを保存
if (isset($data['GET_STATS_DATA']['STATISTICAL_DATA']['DATA_INF']['VALUE'])) {
    foreach ($data['GET_STATS_DATA']['STATISTICAL_DATA']['DATA_INF']['VALUE'] as $value) {
        $category = $conn->real_escape_string($value['@cat01']);
        $val = (int)$value['$'];
        $stmt = $conn->prepare("INSERT INTO estat_data (category, value, user_data_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $category, $val, $user_data_id);
        $stmt->execute();
        $stmt->close();
    }
} else {
    die("No statistical data found in API response.");
}

// CSVデータを読み込み保存
if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $category = $conn->real_escape_string($row[0]);
        $val = (int)$row[1];
        $stmt = $conn->prepare("INSERT INTO csv_data (category, value, user_data_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $category, $val, $user_data_id);
        $stmt->execute();
        $stmt->close();
    }
    fclose($handle);
}

// estat_dataのデータを取得
$sql = "SELECT * FROM gs1_kadai_php01 WHERE user_data_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_data_id);
$stmt->execute();
$result = $stmt->get_result();

echo "gs1_kadai_php01:\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

// csv_dataのデータを取得
$sql = "SELECT * FROM csv_data WHERE user_data_id = ?";
$stmt = $conn->prepare($sql
);