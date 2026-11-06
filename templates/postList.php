<?php
/**
 * postList.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 記事の一覧
 * このサイトは投稿タイプの一覧ページを持たない. 一覧の URL はトップページへ転送し, トップと個別記事の aside を一覧の代わりにする
 */

require_once dirname(__DIR__) . '/inc/ContentLoader.php';
require_once dirname(__DIR__) . '/inc/ContentNavigation.php';
require_once __DIR__ . '/navigation.php';

// 一覧の URL はトップページへ転送する
function renderPostList(array $view): void {
  header('Location: ' . path(), true, 301);
  exit;
}

// 最新記事の aside (記事がなければ出さない)
// $posts: SITE_LATEST_TYPE の全記事. 読み込み済みなら渡す (なければここで読む)
function renderLatestAside(?array $posts = null): void {
  $type = SITE_LATEST_TYPE;
  $config = postType($type);
  $posts ??= (new ContentLoader())->load(siteFile($config['dir']), null, $config['sort']);
  $latest = ContentNavigation::latest($posts, SITE_LATEST_COUNT);
  if (!$latest) return;
?>
        <aside class="main__aside">
          <h2>最新記事</h2>
          <ul class="postIndex">
<?php foreach ($latest as $post) { ?>
            <li class="postIndex__item">
              <a href="<?= e(postUrl($type, $post)) ?>">
<?php $image = ($post['img'] ?? '') ?: SITE_THUMBNAIL_IMAGE; ?>
                <img class="postIndex__image" src="<?= e(path($image)) ?>" alt=""<?= imageAttrs($image) ?>>
<?php if ($config['showDate'] && !empty($post['date'])) { ?>
                <span class="postIndex__date"><?= dateTag($post['date']) ?></span>
<?php } ?>
                <span class="postIndex__title"><?= e($post['title'] ?? '') ?></span>
              </a>
            </li>
<?php } ?>
          </ul>
        </aside>
<?php
}
