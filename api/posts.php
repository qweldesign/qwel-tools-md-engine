<?php
/**
 * 記事の API (JSON)
 *
 * 一覧: api/posts.php?type=blog&page=1&count=10
 *   → { "type", "page", "totalPages", "total", "posts": [{ "title", "slug", "date", "summary", "img", "url" }, ...] }
 *   type: 投稿タイプ (規定で config.php の最初の投稿タイプ)
 *   count: 1ページあたりの件数 (1〜50. 規定で投稿タイプの count, なければ 10)
 * 個別記事: api/posts.php?type=blog&slug=xxx
 *   → { "title", "slug", "date", "summary", "img", "url", "content" (HTML) }
 * img, url は絶対URL. 見つからない場合は 404
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/inc/helpers.php';

header('Content-Type: application/json; charset=UTF-8');
// 他のサイトからも読めるようにする
header('Access-Control-Allow-Origin: *');

function respond(int $status, array $body): void {
  http_response_code($status);
  echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

$type = (string)($_GET['type'] ?? array_key_first(SITE_POST_TYPES));
if (!isset(SITE_POST_TYPES[$type])) respond(404, ['error' => 'unknown type']);

// 記事の公開する項目 (本文は個別記事だけ)
$summarize = fn(array $post): array => [
  'title'   => $post['title'] ?? '',
  'slug'    => $post['slug'] ?? '',
  'date'    => ContentEngine::format_date($post['date'] ?? '', true),
  'summary' => $post['summary'] ?? '',
  'img'     => absolute_url(($post['img'] ?? '') ?: SITE_THUMBNAIL_IMAGE),
  'url'     => absolute_url(post_path($type, $post)),
];

// 個別記事
if (isset($_GET['slug'])) {
  $cms = content_engine($type, ['baseUrl' => absolute_url('')]);
  if (!$cms->is_found()) respond(404, ['error' => 'not found']);
  $post = ['title' => $cms->get_title(), 'slug' => $cms->get_meta('slug'), 'date' => $cms->get_meta('date'),
           'summary' => $cms->get_meta('summary'), 'img' => $cms->get_meta('img')];
  respond(200, $summarize($post) + ['content' => $cms->get_content()]);
}

// 一覧
$count = isset($_GET['count']) ? min(50, max(1, (int)$_GET['count'])) : (post_type($type)['count'] ?? 10);
$cms = content_engine($type, ['count' => $count, 'slug' => null]);
if ($cms->is_not_found()) respond(404, ['error' => 'page out of range']);
respond(200, [
  'type'       => $type,
  'page'       => $cms->get_page(),
  'totalPages' => $cms->get_total_pages(),
  'total'      => count($cms->get_posts(1, PHP_INT_MAX)),
  'posts'      => array_map($summarize, $cms->get_posts()),
]);
