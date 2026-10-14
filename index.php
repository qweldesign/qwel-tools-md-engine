<?php
/**
 * トップページ
 * 左に固定ページ「about」の本文, 右の aside にブログの最新記事を並べる
 * 個別記事は投稿タイプのディレクトリ (blog/, page/) が表示する (一覧ページは持たない)
 */
require_once __DIR__ . '/inc/templates/postList.php';

// トップに表示する固定ページの slug
const TOP_PAGE_SLUG = 'about';

$about = content_engine('page', ['slug' => TOP_PAGE_SLUG, 'page' => 1]);
$latest = content_engine(SITE_LATEST_TYPE, ['slug' => null, 'page' => 1])->get_posts(1, SITE_LATEST_COUNT);

// パンくず (SITE_BREADCRUMB の最後の階層 = このサイトのトップが現在地)
$parents = SITE_BREADCRUMB;
$current = array_pop($parents);
$breadcrumb = new ContentNavigation([
  'parents'     => array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], $parents),
  'subdirLabel' => $current['label'] ?? SITE_NAME,
  'subdirPath'  => path(),
]);

render_header([
  'path' => '',
  'type' => 'website',
]);
?>
    <main id="main" class="main">
      <?= $breadcrumb->breadcrumb() ?>
<?php render_site_heading(); ?>
      <div class="main__row">
        <article class="main__article">
<?php if ($about->is_found()) { ?>
          <div class="entry">
            <h2><?= e($about->get_title()) ?></h2>
<?= $about->get_content() ?>
          </div>
<?php } else { ?>
          <ul class="main__list">
            <li class="main__listItem">Markdown を HTML に変換して表示</li>
            <li class="main__listItem">パンくずリストやページネーションを自動生成</li>
            <li class="main__listItem">データベース不要、ファイルベースで管理</li>
            <li class="main__listItem">PHP のみで動作、軽量・シンプル</li>
          </ul>
<?php } ?>
        </article>
        <aside class="main__aside">
          <h2>最新記事</h2>
<?php render_post_index($latest, SITE_LATEST_TYPE); ?>
        </aside>
      </div>
    </main>
<?php render_footer(); ?>
