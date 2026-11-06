## QWEL Contact Form

軽量・シンプルなお問い合わせフォーム

▶ Sample DEMO: [https://qwel.design/tools/contact-form/]

## セットアップ

1. `inc/` で `composer install` を実行し、Parsedown と PHPMailer を導入する
2. `inc/.env.example` を `inc/.env` としてコピーし、宛先と SMTP の設定を書き換える。サイト名、URL、項目、自動返信の署名は `config.php` に書く
3. `api/`, `assets/`, `blog/`, `contact/`, `content/`, `page/`, `inc/` (`inc/vendor/` を含む), `templates/`, `.htaccess`, `config.php`, `index.php`, `style.css` をサーバーにアップロードする。`inc/vendor/` がないとサイト全体を表示できない

`.env` には SMTPのパスワードが含まれるため Git には含めない。`inc/.htaccess` で `inc/` ごと Web から読めないようにしているが、可能であれば公開ディレクトリの外に置き、`config.php` の `SITE_CONTACT` の `env` を書き換えるとより安全。

### 構成

```
config.php                             サイトの設定 (Webからは読めない)
index.php                              トップページ (サイトが自由に書き換える)
blog/                                  投稿タイプ「blog」の入口 (index.php, .htaccess)
page/                                  固定ページの入口 (index.php だけ。/xxx/ で開く)
content/                               原稿 (Webからは読めない)
contact/                               入力・確認・完了の画面の入口
api/send.php                           送信 (確認画面から JSONで POST される。Webに公開する)
inc/                                   PHPの部品・設定 (Webからは読めない)
  env.php                              .env の読み込み
  ContactForm.php                      フォームの検証とメール送信
  ContentLoader.php                    原稿の読み込み
  ContentNavigation.php                ページ送り, パンくず, 前後の記事, 最新記事のデータ
  ContentPage.php                      投稿タイプのURLを受けて, 画面のテンプレートを選ぶ
  helpers.php                          テンプレートで使う関数
  composer.json                        Parsedown, PHPMailer (composer install で inc/vendor/ に入る)
  .env.example                         設定の見本 (.env としてコピーして使う)
templates/                             サイトが持つテンプレート (Webからは読めない)
  site.php                             共通のヘッダーとフッター
  navigation.php                       パンくず, ページ送り, 前後の記事のHTML
  postList.php, postSingle.php, page.php, notFound.php  画面ごとのHTML
  contact.php                          フォームの 3 画面のHTML
```

設定に不備があると `send.php` は 500 を返し、内容をサーバーのエラーログに残す。

## 迷惑メール対策

- Origin / Referer のチェック: 送信元の `scheme://host[:port]` が `SITE_URL` と完全に一致しない送信は 403 で弾く
- ハニーポット: 人には見えない入力欄 (`templates/contact.php` の `data-contact-honeypot`) に値があれば、送ったふりをして破棄する
- 送信間隔の制限: 同じセッションから `SITE_CONTACT` の `interval` 秒以内に続けて送信すると 429 を返す (`0` で制限しない)

入力の不備 (必須項目が空、メールアドレスの形式など) は 400 と、項目ごとのメッセージを JSON で返し、確認画面に表示する。

## ライセンス | License

MIT License

詳しくは LICENSE ファイルをご覧ください。  
See the LICENSE file for details.  

---

## 制作者 | Author

[QWEL.DESIGN](https://qwel.design)  
福井を拠点に活動するフロントエンド開発者  
Front-end developer based in Fukui, Japan  
