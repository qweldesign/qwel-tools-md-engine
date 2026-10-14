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
api.php                    記事の API (JSON)
content/blog/*.md          原稿 (投稿タイプごとにディレクトリを分ける)
content/images/            原稿用の画像
inc/                       エンジン (ContentEngine など) とテンプレート
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

### API

- 一覧: `api.php?type=blog&page=1&count=10` → `{ type, page, totalPages, total, posts: [{ title, slug, date, summary, img, url }] }`
- 個別記事: `api.php?type=blog&slug=xxx` → `{ title, slug, date, summary, img, url, content }`

---

## 目的・背景 | Purpose & Background

- 環境構築を手軽に行える, 軽量な CMS が欲しかった
- UIの実装・運用・保守に伴うコストとセキュリティリスクを抑えたかった
- クライアントから文章と画像を提供してもらい, 更新自体はエンジニアが行う運用方針でOK
- Markdown をそのまま運用することで, Git 管理による保守が可能な環境を試してみたかった
- 外部依存は最小限 (Markdown パーサーのみ) に抑え, シンプルな設計を目指す

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
