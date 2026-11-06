<?php
/**
 * トップページ
 * 一覧: /, 個別記事: /?slug=xxx
 * 原稿は content/blog/*.md に置く
 */

require_once __DIR__ . '/inc/vendor/autoload.php';
require_once __DIR__ . '/inc/ContentLoader.php';
require_once __DIR__ . '/templates/site.php';

$dir = __DIR__ . '/content/blog';
$loader = new ContentLoader();

// slug があれば個別記事を探す. 配列などの文字列でない値は, 見つからない扱いにする
$isSingle = isset($_GET['slug']);
$slug = $_GET['slug'] ?? null;
$post = $isSingle && is_string($slug) ? $loader->find($dir, $slug) : null;
$notFound = $isSingle && $post === null;
$posts = $isSingle ? [] : $loader->load($dir);

if ($notFound) http_response_code(404);

$heading = $notFound ? 'ページが見つかりません' : ($post['title'] ?? SITE_NAME);

renderHeader([
  'title'       => $isSingle ? $heading : '',
  'description' => $post['summary'] ?? '',
  'noindex'     => $notFound,
]);
?>
    <main class="main">
      <h1 class="main__title"><?= e($heading) ?></h1>
<?php if ($notFound) { ?>
      <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
      <p><a href="<?= e(path()) ?>">記事の一覧へ</a></p>
<?php } elseif ($post) { ?>
      <p><?= dateTag($post['date'] ?? null) ?></p>
      <?= $loader->toHtml($post['content']) ?>

      <p><a href="<?= e(path()) ?>">記事の一覧へ</a></p>
<?php } elseif ($posts) { ?>
      <ul>
<?php foreach ($posts as $item) { ?>
        <li>
          <?= dateTag($item['date'] ?? null) ?>

          <a href="<?= e(path('?slug=' . rawurlencode($item['slug'] ?? ''))) ?>"><?= e($item['title'] ?? '') ?></a>
<?php if (!empty($item['summary'])) { ?>
          <p><?= e($item['summary']) ?></p>
<?php } ?>
        </li>
<?php } ?>
      </ul>
<?php } else { ?>
      <p>まだ記事はありません。</p>
<?php } ?>
    </main>
<?php renderFooter(); ?>
