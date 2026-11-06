<?php
/**
 * トップページ
 * このファイルはサイトが持つもの. 自由に書き換えてよい
 * 見本として, サイトの説明と最新記事を出す
 */

require_once __DIR__ . '/inc/ContentLoader.php';
require_once __DIR__ . '/inc/ContentNavigation.php';
require_once __DIR__ . '/templates/postList.php';

$blog = postType('blog');
$posts = (new ContentLoader())->load(siteFile($blog['dir']), null, $blog['sort']);
$latest = ContentNavigation::latest($posts, 3);

renderHeader();
?>
    <main class="main">
      <h1 class="main__title"><?= e(SITE_NAME) ?></h1>
      <p><?= e(SITE_DESCRIPTION) ?></p>
<?php if ($latest) { ?>
      <h2>最新の<?= e($blog['label']) ?></h2>
<?php renderPostItems($latest, 'blog'); ?>
      <p><a href="<?= e(typeUrl('blog')) ?>"><?= e($blog['label']) ?>の一覧へ</a></p>
<?php } ?>
    </main>
<?php renderFooter(); ?>
