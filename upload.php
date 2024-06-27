<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dataTitle = $_POST['dataTitle'];
    $year = $_POST['year'];

    if (isset($_FILES['file'])) {
        $fileError = $_FILES['file']['error'];
        if ($fileError == 0) {
            $uploadedFile = $_FILES['file'];
            $uploadDir = 'uploads/';
            $uploadFilePath = $uploadDir . basename($uploadedFile['name']);

            // ディレクトリが存在しない場合、作成する
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // CSVファイルを移動する
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadFilePath)) {
                // セッションにデータを保存
                $_SESSION['dataTitle'] = $dataTitle;
                $_SESSION['year'] = $year;
                $_SESSION['csvFilePath'] = $uploadFilePath;

                // analyze_data.php へリダイレクト
                header("Location: ./analyze_data.php");
                exit();
            } else {
                echo "Error uploading CSV file.";
            }
        } else {
            echo "Error: " . $fileError;
            switch ($fileError) {
                case UPLOAD_ERR_INI_SIZE:
                    echo " - The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo " - The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo " - The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo " - No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo " - Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo " - Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo " - A PHP extension stopped the file upload.";
                    break;
                default:
                    echo " - Unknown upload error.";
                    break;
            }
        }
    } else {
        echo "No file uploaded.";
    }
}
?>
