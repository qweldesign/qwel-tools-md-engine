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
