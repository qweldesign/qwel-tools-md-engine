<?php
/**
 * トップページ
 * 左に固定ページ「about」の本文, 右の aside にブログの最新記事を並べる
 */

require_once __DIR__ . '/inc/vendor/autoload.php';
require_once __DIR__ . '/templates/postList.php';

$loader = new ContentLoader();
$about = $loader->find($loader->load(siteFile(postType('page')['dir'])), 'about');

renderHeader(['path' => '', 'type' => 'website']);
?>
    <main class="main">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs(SITE_BREADCRUMB)); ?>
<?php renderSiteHeading(); ?>
      <div class="main__row">
        <article class="main__article">
<?php if ($about) { ?>
          <div class="entry">
            <h2><?= e($about['title'] ?? '') ?></h2>
<?= $loader->toHtml($about['content'], path()) ?>

          </div>
<?php } ?>
        </article>
<?php renderLatestAside(); ?>
      </div>
    </main>
<?php renderFooter(); ?>
