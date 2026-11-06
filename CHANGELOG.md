# 変更履歴 | Changelog

---

## [0.1.0] コアの制作

Markdown の原稿の表示からお問い合わせフォームまで、サイトに必要なコアを作る。

### 原稿

- Markdown と frontmatter で原稿を書く (値はすべて文字列)
- `date` は `2026-10-08` または `2026-10` の形で書く
- 画像は `content/images/` に置き、`/content/images/...` の形で書く

### 投稿タイプとページ

- 投稿タイプを `config.php` の `SITE_POST_TYPES` で増やせる
- 一覧、個別記事、ページ送り、パンくず、前後の記事、最新記事のデータ
- 固定ページを `/slug/` で表示する
- 個別のページを持たない投稿タイプ (`items: false`)
- `order` での並び替え (`sort: order`)
- パンくずから投稿タイプの階層を除く (`breadcrumb: false`)
- 記事がない一覧に出す文 (`empty`)

### URL

- `/blog/xxx/`, `/blog/page/2/` の形の URL。`?slug=xxx`, `?page=2` と `/page/xxx/` からは 301 で転送する
- `php -S` のときだけ `?slug=xxx` の形になる (`SITE_PRETTY_URLS`)

### テンプレート

- エンジンは `inc/`、サイトが持つテンプレートは `templates/` に分ける
- 共通のヘッダーとフッター、title, description, フォント, Google Analytics, favicon
- テンプレートに渡す `$view` に、投稿タイプの全記事 (`posts`) を含める
- エンジンの版 (`SITE_ENGINE_VERSION`) を HTML のコメントに出す

### お問い合わせフォーム

- 入力 → 確認 → 完了 の 3 画面を `SITE_CONTACT_FIELDS` から作る
- 管理者宛と自動返信のメールを SMTP で送る (PHPMailer)。宛先と SMTP の設定は `inc/.env`
- 入力画面を別の場所に置ける (`formUrl`)
- 迷惑メール対策: Origin と Referer の確認、見えない入力欄、送信間隔の制限

### サーバー

- `config.php`, `inc/`, `templates/`, `content/` (`content/images/` の画像を除く) は `.htaccess` で直接のアクセスを禁止する

### 動作環境

- PHP 8.0 以上
- Apache (mod_rewrite, `.htaccess` を有効にする `AllowOverride`)
- Composer

[0.1.0]: https://github.com/qweldesign/simple-cms/releases/tag/v0.1.0

---

## [1.0.0] 公開の準備

お客さまのサイトに使える状態にする。検索エンジンに正しく情報を伝え、公開の前に設置の抜けを確かめられるようにする。

### 検索エンジン

- canonical と OGP を出力する (`noindex` のページに canonical は出さない。2 ページ目以降の canonical はそのページ自身を指す)
- OGP 画像の既定値 (`SITE_OGP_IMAGE`, `SITE_OGP_IMAGE_ALT`) と `og:locale` (`SITE_OG_LOCALE`)
- `/sitemap.xml` を原稿から作る (投稿タイプの `list` で一覧を除き、`SITE_PAGES` でほかのページを足す)
- `/robots.txt` を作る (`SITE_ROBOTS_DISALLOW`)
- 構造化データ (JSON-LD): パンくずと個別記事。種類は投稿タイプの `schema`、著者は `SITE_AUTHOR`
- 年月だけの日付の記事と、日付を出さない投稿タイプには `datePublished` を出さない

### 404

- 存在しないURLを、サイトのレイアウトの 404 で表示する (`ErrorDocument`, `noindex`)

### 健全性チェック

- `php inc/check.php` で、設定、必要なファイル、原稿、お問い合わせフォームの準備を確かめる
- `--http` で、公開中のサイトの `.htaccess` の働きを確かめる (拒否、きれいなURL、転送、404, `/sitemap.xml`, `/robots.txt`)
- 問題があれば `[NG]` と終了コード 1 を返す

### 構成

- `config.php` を `templates/config.php` に移す
- `404.php`, `sitemap.php`, `robots.php` を `inc/` に移す (`inc/.htaccess` でこの 3 つだけ公開する)
- `reset-labels.sh` を `.github/` に移す

### サーバー

- `www` の統一の見本を、ルートの `.htaccess` にコメントで書く
- 日付の判定と表示のタイムゾーンを `templates/config.php` で設定する

### 0.1.0 からの更新

- `.htaccess` の `ErrorDocument` のパスを `inc/404.php` に書き換え、`.htaccess` もアップロードする
- `config.php` の値を `templates/config.php` に移す

[1.0.0]: https://github.com/qweldesign/simple-cms/releases/tag/v1.0.0
