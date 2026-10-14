<?php
/**
 * トップページ
 * このエンジンの紹介と, ブログの最新記事を並べる
 * 一覧・個別記事は投稿タイプのディレクトリ (blog/, page/) が表示する
 */
require_once __DIR__ . '/inc/templates/postList.php';

$blog = content_engine('blog', ['slug' => null, 'page' => 1]);
$latest = $blog->get_posts(1, 3);

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
      <h1 class="main__title">Simple CMS | Created by <span> ― QWEL.DESIGN</span></h1>
      <ul class="main__list">
        <li class="main__listItem is-spec">Markdown を HTML に変換して表示</li>
        <li class="main__listItem is-spec">パンくずリストやページネーションを自動生成</li>
        <li class="main__listItem is-spec">データベース不要、ファイルベースで管理</li>
        <li class="main__listItem is-spec">PHP のみで動作、軽量・シンプル</li>
      </ul>
      <h2 class="text-center my-large">SAMPLE BLOG</h2>
<?php render_post_list($latest, 'blog'); ?>
      <p class="text-center my-large"><a href="<?= e(type_url('blog')) ?>">記事の一覧へ</a></p>
    </main>
<?php render_footer(); ?>
