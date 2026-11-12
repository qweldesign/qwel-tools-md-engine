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

[0.1.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v0.1.0

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

[1.0.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.0.0

---

## [1.1.0] 原稿の下書きとデータファイル

原稿を書く人と設置する人の作業を楽にする。公開前の下書きを確かめられるようにし、記事ではない値をテンプレートの外で管理できるようにする。

### 下書き

- `draft: "true"` の原稿は、本番では読み込まない (一覧、個別記事、サイトマップに出ず、URL を直接開いても 404)
- 確認用サーバー (`php -S`) のときだけ読み込み、タイトルに `[下書き]` を付け、個別のページに `noindex` を付ける
- サイトマップには、確認用サーバーでも載せない
- テンプレートの書き換えは要らない (`ContentLoader::load()` の既定で切り替える)

### データファイル

- `contentJson()` で `content/` の JSON を読む。読めないときは空の配列を返し、エラーログに残す。同じ JSON は 1 回のリクエストで 1 度だけ読む
- `siteOption()` で `content/options.json` の値を読む
- `fillOptions()` で文中の `{名前}` を値に置き換える (整数は 3 桁区切り)

### 健全性チェック

- `content/` の中の壊れた JSON を `[NG]` にする
- `inc/vendor/` に Parsedown がないときを `[NG]` にする
- `--http` で、データファイルが公開されていないことを確かめる

### 文書

- 原稿を別のリポジトリで管理する手順を README に書く
- CHANGELOG のリンクを今のリポジトリ名に直す

### 1.0.0 からの更新

- `inc/` を置き換える。`templates/`, `.htaccess`, `content/` の書き換えは要らない
- 原稿に `draft: "true"` と書いていると、公開されなくなる

[1.1.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.1.0

---

## [1.2.0] 表示の速さ

再訪問の表示を速くし、最初の表示を止めるものを減らす。原稿を置き換えたらすぐ反映される動きは変えない。

### ブラウザのキャッシュ

- `.htaccess` で静的ファイルに `Cache-Control` を付ける (mod_headers)。`?v=` が付いた URL は 1 年、`?v=` のない画像とフォントは 7 日、`?v=` のない CSS と JS は 1 日。HTML には付けない
- `assetUrl()` で URL にファイルの更新時刻を付ける。CSS, JS, ロゴ, favicon, アイコンに使う

### 画像

- `imageAttrs()` で、手元の画像に `width`, `height` と読み込み方の属性を付ける
- 本文の画像に自動で付ける。1 枚目は `eager`、2 枚目以降は `lazy`。原稿の HTML に `width` か `loading` を書いた画像には触らない
- 一覧の先頭のサムネイルに `fetchpriority="high"` を付ける
- `preloadImage()` で、LCP の候補の画像を先に読める

### Web フォント

- `SITE_FONTS_URL` の CSS を、表示を止めずに読む。読み終わるまでは代わりのフォントで表示する (JavaScript が無効のときは今までどおり)

### 健全性チェック

- `--http` で、キャッシュの期間を確かめる。付かないときは `[WARN]`
- `--http` で、トップに長いキャッシュが付いていないことを確かめる

### 1.1.0 からの更新

`inc/` を置き換える。本文の画像の属性は、これだけで効く。ほかは任意で、次の順に取り入れる。

- `.htaccess` にキャッシュの設定を足す。`expr=` は Apache 2.4.10 以上が必要で、古いサーバーでは 500 になるので、上げたあとトップを開いて確かめる
- `templates/site.php` の CSS, JS, ロゴ, favicon, アイコンの `path()` を `assetUrl()` に変える
- `templates/site.php` のフォントの読み方を変える
- `templates/postList.php` の先頭のサムネイルに `imageAttrs()` を使う

キャッシュの設定を入れると、原稿の画像を同じ名前で置き換えたときに、再訪問の人には最大 7 日古いままになる。すぐ反映したいときは、ファイル名を変える。

[1.2.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.2.0

---

## [1.2.1] フェーダーとスライダーの修正

### フェーダー

- JS が動く前も、最初のアイテムを表示する。ファーストビューに置いたときに、画像が届いていても JS を待って LCP が遅れていた

### スライダー

- 画像の読み込み後に位置を計算し直す。遅延読み込みでも位置がずれない
- 縦方向のホイールでページのスクロールを奪わない。スライドは横方向のホイールで動く。従来の動きは `data-wheel-vertical` で選べる
- 画面幅ではなく要素の幅で中央に揃える。画面幅いっぱいに置いたときの見た目は変わらない

### 1.2.0 からの更新

- `js/fader.js`, `js/slider.js`, `scss/_fader.scss` を置き換える。`inc/`, `templates/` の書き換えは要らない
- フェーダーを使うサイトは CSS をビルドし直す

修正は `v1.0.0` から切ったコミットなので、古い版のサイトにも新機能なしで当てられる (`e445dad`, `9dbb8c0`)。

[1.2.1]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.2.1

## [1.2.2] スライダーのリンクの名前

### スライダー

- 前後のリンクに `aria-label` を付ける (「前のスライドへ」「次のスライドへ」)。中身がアイコンだけで、リンクの名前がなかった

### 1.2.1 からの更新

- `js/slider.js` を置き換える。`inc/`, `templates/` の書き換えは要らない

[1.2.2]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.2.2

## [1.3.0] 既定の Web フォント

### Web フォント

- 既定の `SITE_FONTS_URL` で `Noto Sans JP` を読み込む。CSS の既定の書体は `Noto Sans JP` なのに読み込んでおらず、OS に入っている環境でだけ使われていた (Mac や iPhone ではヒラギノになっていた)
- 使っていなかった `Noto Sans`, `Noto Serif` と `Cormorant Garamond` を外す。`Cormorant Garamond` を指定していた `.main__title > span` は、どのテンプレートも `<span>` を出さないので消す
- 新しく作るサイトは、日本語の Web フォントで 1 ページに約 1 MB を読み込む。速さを優先するなら `SITE_FONTS_URL` を空にする (README の「Web フォント」)

### 1.2.2 からの更新

- 新しく作るサイトの既定値の変更。既存のサイトは自分の `SITE_FONTS_URL` を持つので、当てなくてよい
- 当てる場合、`templates/config.php` の `SITE_FONTS_URL` は自分の値を残す。題を書き換えたサイトでは `scss/_main.scss` も自分の版を残す

修正は `v1.0.0` から切ったコミットなので、古い版のサイトにも新機能なしで当てられる (`c6ee41e`)。

[1.3.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.3.0

## [1.3.1] 文字を絞って読む Web フォント

### Web フォント

- `SITE_TEXT_FONTS` で、決まった文言だけに使う書体を、文字を絞って読めるようにする。書体のファイルが数 KB で済むので、本文は OS の書体に任せ、ヒーローの見出しなど目立つ所だけ Web フォントにできる
- `textFontUrl()` は、文言から重なる文字と改行を除き、`text=` 付きの Google Fonts の URL を作る。文字を手で拾わなくてよい
- `SITE_FONTS_URL` と同じく、表示を止めずに読む

### 1.3.0 からの更新

- 使わないサイトは当てなくてよい。`SITE_TEXT_FONTS` がない `config.php` では何も出力しない
- 使うサイトは `inc/helpers.php` を置き換え、`templates/site.php` に読み込みの部分を足し、`templates/config.php` に `SITE_TEXT_FONTS` を書く。`site.php` と `config.php` はサイトが書き換えているので、競合したら自分の版に足す

`v1.0.0` から切ったブランチに積んだコミットなので、古い版のサイトにも当てられる (`d3c6702`)。

[1.3.1]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.3.1

## [1.4.0] 画像の WebP

### 画像

- JPEG と PNG の画像から、幅ごとの WebP を自動で作り、`imageAttrs()` が `srcset` で配る (`inc/ImageVariants.php`)。FTP で画像を上げるだけでよい (#11)
- 作るのはページを表示したとき。1 回の表示で約 2 秒分まで作り、残りは次の表示で作る。`.htaccess` の書き換えも、外から呼べる生成用の入口も要らない
- 幅は `SITE_IMAGE_WIDTHS` (既定は `[384, 768, 1152]`)。元の画像より大きい幅は作らない。元の画像が最大の幅より大きいときは、元の画像も候補に入れる
- 作った画像は `cache/images/` に置く。いつ消してもよい。元の画像と更新時刻が違えば作り直す
- EXIF の向きを直し、PNG の透明を残す。`width` と `height` も、向きを直したあとの値にする (縦の写真で縦横が逆になっていた)
- `imageSizes()` は、`style.css` のブレイクポイントとコンテナ幅から `sizes` を作る。`scss/_root.scss` でブレイクポイントを CSS 変数として出力する
- 原稿の本文の画像と一覧のサムネイルの `sizes` は `SITE_CONTENT_IMAGE_SIZES`
- `check.php --http` で、トップの画像に WebP が付いて開けるかを確かめる

### 1.3.1 からの更新

- `inc/ImageVariants.php` を足し、`inc/helpers.php`, `inc/ContentLoader.php`, `inc/check.php` を置き換える
- `scss/_root.scss` を当て、`style.css` をビルドし直す
- `templates/config.php` に `SITE_IMAGE_WIDTHS` と `SITE_CONTENT_IMAGE_SIZES` を書く。本文の幅を変えたサイトは、`SITE_CONTENT_IMAGE_SIZES` を自分の本文の幅にする
- `.gitignore` に `/cache/` を足す
- テンプレートの画像には、`imageAttrs()` の第 3 引数で `imageSizes()` を渡す。渡さないと `100vw` になる (粗くはならないが、デスクトップで大きな画像を読む)。表示幅を測ってから決め、実際より小さく見積もらない
- 使わないサイトは `SITE_IMAGE_WIDTHS` を空の配列にする。書かない古い `config.php` でも今までどおり動く
- マージでは `templates/config.php`, `templates/postList.php`, `style.css` が競合しやすい。前の 2 つは自分の版に足し、`style.css` はビルドし直す

[1.4.0]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.4.0

## [1.4.1] 画像の sizes の修正と, 画像ごとの WebP の指定

### 画像

- `imageSizes()` が、rem で書いたコンテナ幅を 16px で換算していた。`$font-size-base` を変えたサイト (例: 18px) では、表示する幅を小さく見積もり、画像が粗くなることがあった。`scss/_root.scss` で `--root-font-size` を出力し、その値で換算する
- コンテナ幅が端数の px (例: 710.4px) になると、コンテナが内寸で止まる区切りが消え、`100vw` になっていた。区切りの幅を切り上げてから比べる
- `imageAttrs()` に `webp: false` を渡すと、その画像には WebP を付けない。色数を絞って軽く作った PNG は、WebP にしても軽くならず、質感が柔らかくなることがある。`v1.4.0` では画像ごとに外せなかった

### 1.4.0 からの更新

- `inc/ImageVariants.php` と `inc/helpers.php` を置き換え、`scss/_root.scss` を当てて `style.css` をビルドし直す
- `$font-size-base` とコンテナ幅を既定のまま使っているサイトは、`sizes` の出力が変わらない
- `--root-font-size` がない古い `style.css` では、今までどおり 16px で換算する

[1.4.1]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.4.1

## [1.4.2] 画像の WebP の既定値の見直し

### 画像

- `SITE_IMAGE_WIDTHS` の既定を `[768, 1200]` にする。`sizes` を詰めない既定のままでは 384 は選ばれず、1152 は 3 倍の画面の iPhone (1170〜1179px) に少し足りなかった。768 は 2 倍前後の画面 (PageSpeed の端末は 412px × 1.75 = 721px)、1200 は 3 倍の画面を受け持つ。JPG は幅を変えなくても WebP にすると 4〜6 割軽くなるので、iPhone にも WebP を配る。PageSpeed だけを考えるなら `[768]` でもよい
- `SITE_CONTENT_IMAGE_SIZES` の既定を空の配列にし、本文の画像と一覧のサムネイルの `sizes` を `100vw` にする。前の既定 (エンジンの `.main` の内寸 720px) は、本文の幅を変えたサイトで表示幅を小さく見積もり、画像が粗くなることがあった
- README を組み直す。既定のままで使え、`sizes` を詰めるのは任意とした (詰めるには表示幅を測って確かめる手間がかかり、効くのは主にデスクトップと小さな画像)

### 1.4.1 からの更新

- 新しく作るサイトの既定値の変更。`inc/` の書き換えはない (コメントだけ)
- 既存のサイトは、`templates/config.php` の `SITE_IMAGE_WIDTHS` と `SITE_CONTENT_IMAGE_SIZES` を自分で決める。既定に合わせるなら `[768, 1200]` と `[]` にする

[1.4.2]: https://github.com/qweldesign/qwel-tools-md-engine/releases/tag/v1.4.2
