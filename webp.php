<?php
// クエリパラメータから画像ファイル名と品質を取得
$imagePath = $_GET['src'];
$quality = isset($_GET['q']) ? (int)$_GET['q'] : 5; // デフォルト品質は5

// 品質の範囲を1から10に制限
$quality = max(1, min(10, $quality));

// ファイル名に外部ドメインが含まれていないことを確認
if (preg_match('/https?:\/\//', $imagePath)) {
  header("HTTP/1.1 400 Bad Request");
  exit('External domain sources are not allowed.');
}

// キャッシュフォルダのパス
$cacheFolder = 'cache/';

// キャッシュファイル名（パスと品質レベルに基づく）
$cacheFileName = $cacheFolder . md5($imagePath . $quality) . '.webp';

// キャッシュファイルが存在する場合は、それを使用
if (file_exists($cacheFileName)) {
  header('Content-Type: image/webp');
  readfile($cacheFileName);
  exit;
}

// 画像ファイルの存在を確認
if (!file_exists($imagePath)) {
  header("HTTP/1.1 404 Not Found");
  exit('File not found.');
}

// 画像リソースを作成
$image = null;
switch (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION))) {
  case 'jpg':
  case 'jpeg':
    $image = imagecreatefromjpeg($imagePath);
    break;
  case 'png':
    $image = imagecreatefrompng($imagePath);
    break;
  default:
    header("HTTP/1.1 400 Bad Request");
    exit('Unsupported file type.');
}

// WebP形式で画像をキャッシュに保存
if ($image !== null) {
  // ディレクトリが存在しない場合は作成
  if (!is_dir($cacheFolder)) {
    mkdir($cacheFolder, 0755, true);
  }
  header('Content-Type: image/webp');
  imagewebp($image, $cacheFileName, $quality * 10); // GDの品質は0から100
  readfile($cacheFileName);
  imagedestroy($image);
}
?>
