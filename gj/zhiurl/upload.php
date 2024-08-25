<?php
$uploadDir = 'uploads/';
$forbiddenExtensions = ['exe', 'scr', 'bat', 'cmd', 'bin', 'php', 'asp', 'aspx', 'jsp', 'sh', 'vbs', 'ps1', 'ini', 'conf', 'mdb', 'c', 'cpp', 'java'];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileName = $_FILES['file']['name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $forbiddenExtensions)) {
        echo '禁止上传的文件类型。';
        return;
    }

    $tempFilePath = $_FILES['file']['tmp_name'];
    $existingFiles = scandir($uploadDir);
    $currentFileHash = hash_file('md5', $tempFilePath);
    foreach ($existingFiles as $existingFile) {
        if ($existingFile!== '.' && $existingFile!== '..') {
            $existingFilePath = $uploadDir. $existingFile;
            $existingFileHash = hash_file('md5', $existingFilePath);
            if ($currentFileHash === $existingFileHash) {
                // 文件哈希值相同，直接返回现有文件的直链
                echo 'https://deron.space/gj/zhiurl/uploads/'. $existingFile;
                return;
            }
        }
    }

    $filePath = $uploadDir. $fileName;
    if (move_uploaded_file($tempFilePath, $filePath)) {
        // 记录上传者 IP、文件名和上传时间
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ipCandidate) {
                $ipCandidate = trim($ipCandidate);
                if (!in_array($ipCandidate, ['127.0.0.1', '::1'])) {
                    $ip = $ipCandidate;
                    break;
                }
            }
            if (!isset($ip)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $logFilePath = 'upload_log.txt';
        $uploadTime = date('Y-m-d H:i:s');
        $logContent = "IP: {$ip} uploaded file: {$fileName} at {$uploadTime}\n";
        file_put_contents($logFilePath, $logContent, FILE_APPEND);

        // 文件不存在，上传并返回直链
        echo 'https://deron.space/gj/zhiurl/uploads/'. $fileName;
    } else {
        echo '文件上传失败';
    }
} else {
    echo '文件上传错误';
}
?>
