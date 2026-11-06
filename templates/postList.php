<?php
/**
 * postList.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 投稿タイプの一覧のページ
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   posts: 投稿タイプの全記事 (最新記事などに使う)
 *   title: ページのタイトル
 *   pagination: ContentNavigation::paginate() の戻り値
 *   crumbs: パンくず
 */
function renderPostList(array $view): void {
  $config = $view['config'];
  $posts = $view['pagination']['posts'];
  renderHeader(['title' => $view['title'], 'description' => $config['lead']]);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($config['label']) ?></h1>
<?php if ($config['lead']) { ?>
      <p><?= e($config['lead']) ?></p>
<?php } ?>
<?php if ($posts) { ?>
<?php renderPostItems($posts, $view['type']); ?>
<?php renderPagination($view['pagination'], fn(int $number): string => pageUrl($view['type'], $number)); ?>
<?php } else { ?>
      <p><?= e($config['empty']) ?></p>
<?php } ?>
    </main>
<?php
  renderFooter();
}

// 記事の並び (一覧のページとトップページで使う). 画像は原稿の img, なければ SITE_THUMBNAIL_IMAGE
function renderPostItems(array $posts, string $type): void {
  $showDate = postType($type)['showDate'];
?>
      <ul class="postList is-list">
<?php foreach ($posts as $post) { ?>
<?php $url = postUrl($type, $post); ?>
<?php $image = ($post['img'] ?? '') ?: SITE_THUMBNAIL_IMAGE; ?>
        <li class="postList__item postItem">
<?php if ($image) { ?>
          <figure class="postItem__image">
            <img src="<?= e(path($image)) ?>" alt="" loading="lazy">
          </figure>
<?php } ?>
          <div class="postItem__content">
<?php if ($showDate && !empty($post['date'])) { ?>
            <p class="postItem__info"><?= dateTag($post['date']) ?></p>
<?php } ?>
            <h2 class="postItem__heading"><a href="<?= e($url) ?>"<?= isExternal($url) ? ' target="_blank" rel="noopener"' : '' ?>><?= e($post['title'] ?? '') ?></a></h2>
<?php if (!empty($post['summary'])) { ?>
            <p class="postItem__excerpt"><?= e($post['summary']) ?></p>
<?php } ?>
          </div>
        </li>
<?php } ?>
      </ul>
<?php
}
