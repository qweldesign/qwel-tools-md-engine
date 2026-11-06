<?php
/**
 * check.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 設置の健全性チェック (コマンドラインから実行する)
 *   php inc/check.php              手元のファイル (設定, 原稿, お問い合わせフォームの準備)
 *   php inc/check.php --http       あわせて公開中のサイト (SITE_URL + SITE_ROOT) に問い合わせる
 *   php inc/check.php --http=URL   問い合わせ先のサイトのルートの URL を指定する (例: https://staging.example.com/)
 *
 * .htaccess はドットファイルで, inc/vendor/ は Git に入らない. どちらも上げ忘れやすく, 手元では気付けないので, ここで確かめる
 * [NG] があれば終了コード 1 を返す. [WARN] は表示が壊れないものなので, 終了コードに含めない
 * .env の値は出力しない
 */

// Web からは実行しない (.htaccess が効いていない場合にも, 設定の中身を出さないように)
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

$root = dirname(__DIR__);
require_once $root . '/templates/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ContentLoader.php';
require_once __DIR__ . '/env.php';

$failures = 0;
$warnings = 0;

function report(string $status, string $message): void {
  global $failures, $warnings;
  if ($status === 'NG') $failures++;
  if ($status === 'WARN') $warnings++;
  echo "  [{$status}] {$message}\n";
}

function check(bool $ok, string $message): void {
  report($ok ? 'OK' : 'NG', $message);
}

// 原稿の日付の形 (2026-10-09, または年月だけの 2022-08)
function isValidDate(string $date): bool {
  if (!preg_match('/^(\d{4})-(\d{2})(?:-(\d{2}))?$/', $date, $parts)) return false;
  return checkdate((int)$parts[2], (int)($parts[3] ?? 1), (int)$parts[1]);
}

// ---- 設定 ----
echo "設定\n";
check(PHP_VERSION_ID >= 80000, 'PHP ' . PHP_VERSION . ' (8.0 以上)');
check((bool)preg_match('#^https?://[^/]+$#', SITE_URL), 'SITE_URL が http から始まり, 末尾にスラッシュがない');
check(str_starts_with(SITE_ROOT, '/') && str_ends_with(SITE_ROOT, '/'), 'SITE_ROOT がスラッシュで始まり, スラッシュで終わる');
check(is_file(__DIR__ . '/vendor/autoload.php'), 'inc/vendor/ がある (inc/ で composer install. ないとサイト全体を表示できない)');
foreach (['.htaccess', 'inc/.htaccess', 'templates/.htaccess', 'content/.htaccess', 'content/images/.htaccess', 'inc/404.php', 'inc/sitemap.php', 'inc/robots.php'] as $file) {
  check(is_file("{$root}/{$file}"), "{$file} がある");
}
if (is_file("{$root}/.htaccess")) {
  // ErrorDocument はドメインからの絶対パスなので, SITE_ROOT を変えたときに書き換え忘れやすい
  $errorDocument = preg_match('/^\s*ErrorDocument\s+404\s+(\S+)/m', (string)file_get_contents("{$root}/.htaccess"), $match) ? $match[1] : '';
  check($errorDocument === SITE_ROOT . 'inc/404.php', '.htaccess の ErrorDocument が ' . SITE_ROOT . 'inc/404.php' . " ({$errorDocument})");
}
foreach (['SITE_OGP_IMAGE', 'SITE_THUMBNAIL_IMAGE', 'SITE_LOGO'] as $name) {
  $image = defined($name) ? constant($name) : '';
  if ($image === '' || isExternal($image)) continue;
  check(siteFileExists($image), "{$name} ({$image}) がある");
}

// ---- 原稿 ----
echo "原稿\n";
$loader = new ContentLoader();
$samples = []; // HTTP のチェックに使う, 投稿タイプごとの記事を 1 つ
$sampleImage = '';
foreach (array_keys(SITE_POST_TYPES) as $type) {
  $config = postType($type);
  $isRoot = $config['path'] === '';
  $entry = trim($isRoot ? $type : $config['path'], '/');
  $dir = siteFile($config['dir']);

  check(is_file("{$root}/{$entry}/index.php"), "{$type}: {$entry}/index.php がある");
  if (SITE_PRETTY_URLS && !$isRoot) {
    check(is_file("{$root}/{$entry}/.htaccess"), "{$type}: {$entry}/.htaccess がある (SITE_PRETTY_URLS)");
  }
  // 固定ページのリライトが効かなくなるので, ルートに置く投稿タイプの入口には .htaccess を置かない
  if ($isRoot) check(!is_file("{$root}/{$entry}/.htaccess"), "{$type}: {$entry}/.htaccess がない");
  if (!is_dir($dir)) {
    report('WARN', "{$type}: 原稿のディレクトリ {$config['dir']} がない");
    continue;
  }

  $files = glob("{$dir}/*.md") ?: [];
  $slugs = [];
  $problems = 0;
  foreach ($files as $file) {
    $name = basename($file);
    $post = $loader->read($file);
    if ($post === null) {
      report('NG', "{$type}/{$name}: frontmatter を読めない");
      $problems++;
      continue;
    }

    $required = $config['items'] ? ['title', 'slug'] : ['title', 'url'];
    // 日付の順に並べ, 日付を出す投稿タイプだけ date を求める (固定ページなどには要らない)
    if ($config['sort'] === 'date' && $config['showDate']) $required[] = 'date';
    $missing = array_filter($required, fn(string $key): bool => ($post[$key] ?? '') === '');
    if ($missing) {
      report('NG', "{$type}/{$name}: " . implode(', ', $missing) . ' がない');
      $problems++;
    }

    $date = $post['date'] ?? '';
    if ($date !== '' && !isValidDate($date)) {
      report('NG', "{$type}/{$name}: date \"{$date}\" は使えない (2026-10-09, または 2026-10 の形)");
      $problems++;
    }

    $slug = $post['slug'] ?? '';
    if ($slug !== '') {
      // page はページ送りの URL (/blog/page/2/) と重なる
      if (!preg_match('/^[A-Za-z0-9_-]+$/', $slug) || $slug === 'page') {
        report('NG', "{$type}/{$name}: slug \"{$slug}\" は使えない (英数字, ハイフン, アンダースコア. page は不可)");
        $problems++;
      }
      if (isset($slugs[$slug])) {
        report('NG', "{$type}/{$name}: slug \"{$slug}\" が {$slugs[$slug]} と重なっている");
        $problems++;
      }
      $slugs[$slug] = $name;
      // 実在するフォルダが優先されて, 原稿が黙って隠れる
      if ($isRoot && (is_dir("{$root}/{$slug}") || is_file("{$root}/{$slug}"))) {
        report('NG', "{$type}/{$name}: slug \"{$slug}\" は実在するフォルダと重なっている");
        $problems++;
      }
    }

    $image = $post['img'] ?? '';
    if ($image !== '' && !isExternal($image)) {
      if (!siteFileExists($image)) {
        report('WARN', "{$type}/{$name}: img {$image} がない");
      } elseif ($sampleImage === '' && str_contains($image, 'content/images/')) {
        $sampleImage = trim($image, '/');
      }
    }

    if (!isset($samples[$type]) && $config['items'] && !$missing && $slug !== '') {
      $samples[$type] = ['file' => $name, 'post' => $post];
    }
  }
  if (!$problems) report('OK', "{$type}: 原稿 " . count($files) . ' 件');
}

// ---- お問い合わせフォーム (contact/ のページがあるサイトだけ) ----
$hasContact = defined('SITE_CONTACT') && is_file($root . '/' . trim(SITE_CONTACT['path'], '/') . '/index.php');
if ($hasContact) {
  echo "お問い合わせフォーム\n";
  try {
    $env = loadEnv(SITE_CONTACT['env']);
    foreach (['ADMIN_EMAIL', 'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS'] as $key) {
      check(($env[$key] ?? '') !== '', ".env に {$key} がある");
    }
    // .env.example をコピーしたまま書き換えていない
    check(($env['SMTP_HOST'] ?? '') !== 'smtp.example.com' && ($env['SMTP_PASS'] ?? '') !== '********', '.env の SMTP の設定が見本のままではない');
  } catch (RuntimeException $error) {
    report('NG', $error->getMessage());
  }
}

// ---- 公開中のサイト (HTTP) ----
$args = getopt('', ['http::']);
if (array_key_exists('http', $args)) {
  $base = is_string($args['http']) && $args['http'] !== '' ? rtrim($args['http'], '/') . '/' : SITE_URL . SITE_ROOT;
  echo "公開中のサイト ({$base})\n";

  // ステータスコード, Content-Type, 転送先, 本文 (転送はたどらない)
  $request = function (string $path) use ($base): array {
    $context = stream_context_create(['http' => [
      'method'          => 'GET',
      'ignore_errors'   => true,
      'follow_location' => 0,
      'timeout'         => 10,
      'header'          => "User-Agent: simple-cms-check\r\n",
    ]]);
    $body = @file_get_contents($base . $path, false, $context);
    $headers = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
    $result = ['status' => 0, 'type' => '', 'location' => '', 'body' => $body === false ? '' : $body];
    foreach ($headers as $header) {
      if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $match)) $result['status'] = (int)$match[1];
      if (preg_match('#^Content-Type:\s*(.+)$#i', $header, $match)) $result['type'] = trim($match[1]);
      if (preg_match('#^Location:\s*(.+)$#i', $header, $match)) $result['location'] = trim($match[1]);
    }
    return $result;
  };
  $expect = function (string $path, int $expected, string $note) use ($request): void {
    $result = $request($path);
    $status = $result['status'] ?: '応答なし';
    check($result['status'] === $expected, "/{$path} が {$expected} ({$note}) → {$status}");
  };
  // 301 で, 転送先の末尾が $target になる
  $expectRedirect = function (string $path, string $target, string $note) use ($request): void {
    $result = $request($path);
    check(
      $result['status'] === 301 && str_ends_with($result['location'], $target),
      "/{$path} が /{$target} へ 301 ({$note}) → " . ($result['status'] ?: '応答なし') . ($result['location'] ? " {$result['location']}" : '')
    );
  };

  // 公開しないファイル
  $expect('templates/config.php', 403, '設定を公開しない');
  $expect('inc/helpers.php', 403, 'inc/ を公開しない');
  $expect('inc/.env', 403, '.env を公開しない');
  $expect('templates/site.php', 403, 'templates/ を公開しない');
  foreach ($samples as $type => $sample) {
    $expect(trim(postType($type)['dir'], '/') . '/' . rawurlencode($sample['file']), 403, '原稿を公開しない');
    break;
  }
  if ($sampleImage !== '') $expect($sampleImage, 200, '原稿の画像を公開する');

  // リライト
  $sitemap = $request('sitemap.xml');
  check(
    $sitemap['status'] === 200 && str_contains($sitemap['type'], 'xml'),
    '/sitemap.xml が XML (inc/sitemap.php へのリライト) → ' . ($sitemap['status'] ?: '応答なし') . ($sitemap['type'] ? " {$sitemap['type']}" : '')
  );
  $robots = $request('robots.txt');
  check(
    $robots['status'] === 200 && str_contains($robots['body'], 'Sitemap: '),
    '/robots.txt に Sitemap の行がある (inc/robots.php へのリライト) → ' . ($robots['status'] ?: '応答なし')
  );

  // 記事の URL と転送
  foreach ($samples as $type => $sample) {
    $config = postType($type);
    $slug = rawurlencode($sample['post']['slug']);
    $prefix = $config['path'];
    $entry = $prefix === '' ? $type . '/' : $prefix;
    $expect("{$prefix}{$slug}/", 200, '記事の URL');
    $expectRedirect("{$prefix}{$slug}", "{$prefix}{$slug}/", '末尾のスラッシュ');
    $expectRedirect("{$entry}?slug={$slug}", "{$prefix}{$slug}/", 'きれいな URL へ');
    if ($prefix === '') $expectRedirect("{$entry}{$slug}/", "{$slug}/", '固定ページの入口から');
  }

  // 404 (Apache の既定の画面ではなく, サイトのレイアウトの 404. noindex の有無で見分ける)
  foreach (['simple-cms-check-not-found/' => '固定ページのリライト', 'simple-cms-check/not/found/' => 'ErrorDocument'] as $path => $note) {
    $notFound = $request($path);
    check(
      $notFound['status'] === 404 && str_contains($notFound['body'], 'noindex'),
      "存在しない URL がサイトの 404 のページ ({$note}) → " . ($notFound['status'] ?: '応答なし')
    );
  }

  if ($hasContact) $expect('api/send.php', 405, 'お問い合わせの送信は POST だけ');
}

$summary = $failures ? "問題が {$failures} 件あります" : '問題はありません';
echo "\n{$summary}" . ($warnings ? " (確認をおすすめするものが {$warnings} 件あります)" : '') . "\n";
exit($failures ? 1 : 0);
