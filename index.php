<?php
/**
 * トップページ
 * 一覧: /, 2ページ目以降: /?page=2, 個別記事: /?slug=xxx
 * 原稿は content/blog/*.md に置く
 */

require_once __DIR__ . '/inc/vendor/autoload.php';
require_once __DIR__ . '/inc/ContentLoader.php';
require_once __DIR__ . '/inc/ContentNavigation.php';
require_once __DIR__ . '/templates/navigation.php';

$perPage = 10;
$loader = new ContentLoader();
$allPosts = $loader->load(__DIR__ . '/content/blog');

$postUrl = fn(array $post): string => path('?slug=' . rawurlencode($post['slug'] ?? ''));
$pageUrl = fn(int $number): string => $number === 1 ? path() : path('?page=' . $number);

// slug があれば個別記事を探す. 配列などの文字列でない値は, 見つからない扱いにする
$isSingle = isset($_GET['slug']);
$slug = $_GET['slug'] ?? null;
$post = $isSingle && is_string($slug) ? $loader->find($allPosts, $slug) : null;

$pageNumber = is_string($_GET['page'] ?? null) ? (int)$_GET['page'] : 1;
$pagination = ContentNavigation::paginate($allPosts, $pageNumber, $perPage);

$notFound = $isSingle ? $post === null : $pagination['outOfRange'];
if ($notFound) http_response_code(404);

$pageLabel = $pagination['page'] > 1 ? "{$pagination['page']}ページ目" : '';
$heading = $notFound ? 'ページが見つかりません' : ($post['title'] ?? SITE_NAME);
$current = $notFound || $isSingle ? $heading : $pageLabel;

renderHeader([
  'title'       => $current,
  'description' => $post['summary'] ?? '',
  'noindex'     => $notFound,
]);
?>
    <main class="main">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs([...SITE_BREADCRUMB, ['label' => $current]])); ?>
      <h1 class="main__title"><?= e($heading) ?></h1>
<?php if ($notFound) { ?>
      <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
      <p><a href="<?= e(path()) ?>">記事の一覧へ</a></p>
<?php } elseif ($post) { ?>
      <p><?= dateTag($post['date'] ?? null) ?></p>
      <?= $loader->toHtml($post['content']) ?>

<?php renderPostNav(ContentNavigation::adjacent($allPosts, $post['slug']), $postUrl); ?>
      <p><a href="<?= e(path()) ?>">記事の一覧へ</a></p>
<?php } elseif ($pagination['posts']) { ?>
      <ul>
<?php foreach ($pagination['posts'] as $item) { ?>
        <li>
          <?= dateTag($item['date'] ?? null) ?>

          <a href="<?= e($postUrl($item)) ?>"><?= e($item['title'] ?? '') ?></a>
<?php if (!empty($item['summary'])) { ?>
          <p><?= e($item['summary']) ?></p>
<?php } ?>
        </li>
<?php } ?>
      </ul>
<?php renderPagination($pagination, $pageUrl); ?>
<?php } else { ?>
      <p>まだ記事はありません。</p>
<?php } ?>
    </main>
<?php renderFooter(); ?>
