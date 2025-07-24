<div id="top"></div>

# Tripnote

旅行プランを簡単に記録・共有できるWebアプリケーションです。地図表示やメモ機能を使って、自分だけの旅ノートを作成できます。

---

## 使用技術一覧

<p style="display: inline">
  <img src="https://img.shields.io/badge/-HTML5-000000.svg?logo=html5&style=for-the-badge">
  <img src="https://img.shields.io/badge/-CSS3-000000.svg?logo=css3&style=for-the-badge">
  <img src="https://img.shields.io/badge/-JavaScript-000000.svg?logo=javascript&style=for-the-badge">
  <img src="https://img.shields.io/badge/-PHP-777BB4.svg?logo=php&style=for-the-badge">
  <img src="https://img.shields.io/badge/-MySQL-4479A1.svg?logo=mysql&style=for-the-badge&logoColor=white">
  <img src="https://img.shields.io/badge/-Leaflet-199848.svg?logo=leaflet&style=for-the-badge&logoColor=white">
</p>

---

## 目次
1. [プロジェクトについて](#プロジェクトについて)
2. [機能](#機能)
3. [環境](#環境)
4. [ディレクトリ構成](#ディレクトリ構成)
5. [開発環境構築](#開発環境構築)

---

## プロジェクトについて
Tripnoteは、旅行プランをWeb上で管理できるシンプルなアプリケーションです。地図表示、投稿、写真アップロード、レスポンシブデザインをサポートしています。

<p align="right">(<a href="#top">トップへ</a>)</p>

---

## 機能
- **地図表示**：Leaflet.jsを使用してインタラクティブなマップ表示
- **投稿管理**：タイトル・説明・画像を追加可能
- **編集・削除**：過去の投稿を簡単に管理
- **現在地取得**：ブラウザのGeolocation API対応
- **写真アップロード**
- **レスポンシブデザイン対応**

<p align="right">(<a href="#top">トップへ</a>)</p>

---

## 環境
| 項目        | バージョン |
|------------|-----------|
| PHP        | 8.2.4     |
| MySQL      | 8.0.36    |
| Leaflet.js | 1.9.4     |

<p align="right">(<a href="#top">トップへ</a>)</p>

---

## ディレクトリ構成
```

Tripnote/
│
├── config/
│   └── config.php                # DB接続設定 (DSN, ユーザー, パスワード)
│
├── uploads/                      # 投稿画像保存用フォルダ
│
├── index.php                     # ホームページ (地図 + 投稿一覧 + ソート + 検索)
├── post.php                      # 新規投稿ページ
├── edit.php                      # 投稿編集ページ
├── delete.php                    # 投稿削除処理
├── detail.php                    # 投稿詳細 + コメント表示/追加
│
├── login.php                     # ログインページ
├── register.php                  # 新規ユーザー登録ページ
├── logout.php                    # ログアウト処理
│
├── setup.php                     # DBセットアップ (utf8mb4対応, テーブル作成)
├── check_charset.php             # DB文字コード確認スクリプト
│
└── styles.css                    # 共通CSS（あるだけで使ってない；；）


````

<p align="right">(<a href="#top">トップへ</a>)</p>

---

## 開発環境構築
### 1. リポジトリをクローン
```bash
git clone https://github.com/SatoHinata/Tripnote.git
cd Tripnote
````

### 2. データベース設定

MySQLに以下を作成してください：

```sql
CREATE DATABASE tripnote;
USE tripnote;

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. `backend/db.php` の接続情報を設定

```php
<?php
$dsn = 'mysql:dbname=tripnote;host=localhost;charset=utf8';
$user = 'root';
$password = '';
try {
    $pdo = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
```

### 4. ローカルサーバーで起動

* XAMPPなどでPHPサーバーを起動
* ブラウザで `http://localhost/Tripnote` にアクセス

<p align="right">(<a href="#top">トップへ</a>)</p>
