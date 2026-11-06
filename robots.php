<?php
/**
 * robots.txt (/robots.txt)
 * config.php の設定から作る. /robots.txt へのアクセスは, ルートの .htaccess でここに振り分ける
 * ※ robots.txt はドメインのルートでしか読まれないため, サブディレクトリに置いたサイトでは効かない
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$disallow = defined('SITE_ROBOTS_DISALLOW') ? SITE_ROBOTS_DISALLOW : [];

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
// 拒否するパスがなければ, 空の Disallow (すべて許可) にする
if (!$disallow) echo "Disallow:\n";
foreach ($disallow as $path) {
  echo 'Disallow: ' . path($path) . "\n";
}
echo "\n";
echo 'Sitemap: ' . absoluteUrl('sitemap.xml') . "\n";
