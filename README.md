# QWEL Markdown Engine (Simple CMS)

Markdown ファイルを記事として読み込み、HTML に変換して表示するためのシンプルな PHP エンジン。  
データベース不要で、作品集やブログなどの**軽量なサイト向けの CMS として利用可能**。

▶ Sample DEMO: [https://qwel.design/tools/simple-cms/]

---

## 概要・機能 | Overview & Features

- Markdown を HTML に変換して表示
- データベース不要、Markdown ファイルベースで管理
- パンくずリストやページネーションを自動生成
- ヘッダー・フッター (title, description, canonical, OGP) を設定ファイルから自動生成
- 複数の投稿タイプ (ブログ, 固定ページなど) をディレクトリごとに管理
- `/blog/xxx/` 形式の URL, サイトマップの自動生成
- 記事取得のための API 有り
- お問い合わせフォーム (入力 → 確認 → 完了. 管理者宛と自動返信のメールを SMTP で送信)
- PHP のみで動作, 軽量・シンプル

---

## 使い方 | Usage

### 構成

```
config.php                 サイトの設定 (サイト名, URL, ナビ, 投稿タイプなど)
index.php                  トップページ
blog/index.php, .htaccess  投稿タイプ「blog」の一覧と個別記事
page/index.php, .htaccess  投稿タイプ「page」(固定ページ) の一覧と個別のページ
sitemap.php                サイトマップ (/sitemap.xml)
api/posts.php              記事の API (JSON)
api/send.php               お問い合わせフォームの送信 (JSON)
content/blog/*.md          原稿 (投稿タイプごとにディレクトリを分ける)
content/images/            原稿用の画像
contact/                   お問い合わせフォーム (入力・確認・完了の画面)
inc/                       エンジン (ContentEngine など) とテンプレート (Web からは読めない)
inc/composer.json          お問い合わせフォームのメール送信に使う PHPMailer (composer install で inc/vendor/ に入る)
inc/.env.example           お問い合わせフォームの設定 (宛先・SMTP) の見本 (.env としてコピーして使う)
```

### 設定

サイトごとに変わる値は `config.php` にまとめる。`inc/` 以下には書かない。

- `SITE_URL`, `SITE_ROOT`: サイトの URL と, ルートのパス (ドメイン直下なら `/`, サブディレクトリなら `/tools/simple-cms/` のように)
- `SITE_NAV`, `SITE_FOOTER_LINKS`, `SITE_SNS`: ヘッダー・フッターのリンク
- `SITE_BREADCRUMB`: パンくずの先頭の階層
- `SITE_POST_TYPES`: 投稿タイプ (表示名, URL, 原稿のディレクトリ, 1ページの件数, 日付の表示)
- `SITE_PRETTY_URLS`: `true` で `/blog/xxx/` 形式, `false` で `?slug=xxx` 形式 (`.htaccess` が使えないサーバー向け)

### 原稿

原稿の冒頭に frontmatter を書く。

```
---
title: "記事のタイトル"
slug: "my-first-post"
img: "/content/images/my-first-post/00.jpg"
date: "2026-10-14"
summary: "一覧や description に使う要約"
---
```

- `title`, `slug` (URL に使う英数字), `date` (並び順) は必須
- `date` は年月だけ (`2022-08`) でもよい (一覧では 2022.08 と表示する)
- `img`: アイキャッチ (任意. なければ `SITE_THUMBNAIL_IMAGE`. OGP 画像にもなる)
- `summary`: 一覧・description に使う要約 (任意)
- `draft: "true"`: 下書き (公開しない)
- 本文中の画像は `./content/images/...` のように書く (記事の URL の階層に関係なく, サイトのルートからのパスに置き換える)

### 投稿タイプを増やす

1. `config.php` の `SITE_POST_TYPES` に追加する (例: `news`)
2. `news/index.php` を作り, `render_posts('news');` を呼ぶ
3. `blog/.htaccess` を `news/.htaccess` にそのままコピーする (場所に依存しない書き方にしている)
4. `content/news/` に原稿を置く

slug に `page` は使えない (ページ送りの `/blog/page/2/` と重なるため)。

### サーバー

- `.htaccess` (Apache の mod_rewrite) で URL を振り分ける。`content/` (画像以外), `inc/`, `config.php` は直接のアクセスを禁止している
- 存在しない記事と, 範囲外のページ番号は 404 を返す

### お問い合わせフォーム

`/contact/` (入力) → `/contact/confirm/` (確認) → `/contact/thanks/` (完了) の3画面。入力内容は sessionStorage で確認画面へ渡し, 確認画面から `api/send.php` へ送信する。

1. `inc/` で `composer install` を実行し、PHPMailer を導入する
2. `inc/.env.example` を `inc/.env` としてコピーし、宛先・SMTP の設定を書き換える
3. `config.php` の `SITE_CONTACT` (タイトル, 説明文, 自動返信の署名など) と `SITE_CONTACT_FIELDS` (項目) を書き換える
4. `inc/` (`inc/vendor/` を含む) ごとサーバーにアップロードする

秘密の設定 (宛先・SMTP) は `inc/.env` に書く (各項目の説明は `inc/.env.example` を参照)。`.env` には SMTP のパスワードが含まれるため Git には含めない。`inc/.htaccess` で `inc/` ごと Web から読めないようにしているが、可能であれば公開ディレクトリの外に置き、`config.php` の `SITE_CONTACT` の `env` を書き換えるとより安全。

設定に不備があると `api/send.php` は 500 を返し、内容をサーバーのエラーログに残す。

- 項目は `SITE_CONTACT_FIELDS` から, 入力欄・確認画面・メール本文・サーバー側の検証をまとめて作る (`text`, `email`, `tel`, `url`, `textarea`, `select`, `radio`, `checkbox`)
- 最初の `email` 項目を, 自動返信の宛先と管理者宛メールの返信先にする
- 迷惑メール対策: Origin / Referer のチェック, 見えない入力欄 (ハニーポット), 同じセッションからの送信間隔の制限 (`SITE_CONTACT` の `interval` 秒)
- 自動返信だけ失敗した場合は, ログに残して送信成功として扱う
- メールの送信には [PHPMailer](https://github.com/PHPMailer/PHPMailer) を Composer で導入して使う (更新は `composer update`)。`inc/vendor/` は Git に含めず, `inc/.htaccess` で Web から読めないようにしている
- `inc/vendor/` がない場合も入力画面などは表示でき, 送信時に 500 を返して理由をエラーログに残す

### API

- 一覧: `api/posts.php?type=blog&page=1&count=10` → `{ type, page, totalPages, total, posts: [{ title, slug, date, summary, img, url }] }`
- 個別記事: `api/posts.php?type=blog&slug=xxx` → `{ title, slug, date, summary, img, url, content }`

---

## 目的・背景 | Purpose & Background

- 環境構築を手軽に行える, 軽量な CMS が欲しかった
- UIの実装・運用・保守に伴うコストとセキュリティリスクを抑えたかった
- クライアントから文章と画像を提供してもらい, 更新自体はエンジニアが行う運用方針でOK
- Markdown をそのまま運用することで, Git 管理による保守が可能な環境を試してみたかった
- 外部依存は最小限 (Markdown パーサーと, お問い合わせフォームのメール送信の PHPMailer (Composer) のみ) に抑え, シンプルな設計を目指す

---

## 設計・実装 | Design & Implementation

- 構成をなるべく小さく, 理解し易く保つ
- 記事管理は Markdown + frontmatter を基本とする
- Loader (読み込み) と Navigation (UI補助) を責務分離
- CMS というより「コンテンツエンジン」として扱う
- 機能よりも可搬性を重視 (ベーシック認証や記事編集画面は作らない)

---

## ライセンス | License

MIT License

このプロジェクトは, お知らせ等簡易ブログシステムの用途として自由に使用できることを目的としています。  
This project is designed to be freely used as a lightweight blog system for announcements.  

詳しくは LICENSE ファイルをご覧ください。  
See the LICENSE file for details.  

---

## 制作者 | Author

[QWEL.DESIGN](https://qwel.design)  
福井を拠点に活動するフロントエンド開発者  
Front-end developer based in Fukui, Japan  
