<?php
/**
 * postSingle.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 個別記事のページ (layout: post). 左に本文, 右の aside に最新記事を並べる
 */

require_once __DIR__ . '/postList.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   posts: 投稿タイプの全記事 (最新記事などに使う)
 *   path: このページのパス (canonical に使う)
 *   post: 記事
 *   content: 本文の HTML
 *   adjacent: 前後の記事
 *   crumbs: パンくず
 *   head: 構造化データ (renderHeader() の head に渡す)
 */
function renderPostSingle(array $view): void {
  $post = $view['post'];
  $config = $view['config'];
  renderHeader(['title' => $post['title'] ?? '', 'description' => $post['summary'] ?? '', 'path' => $view['path'], 'image' => $post['img'] ?? '', 'head' => $view['head']]);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
<?php renderSiteHeading(); ?>
      <div class="main__row">
        <article class="main__article">
          <div class="entry">
            <h2><?= e($post['title'] ?? '') ?></h2>
<?php if ($config['showDate'] && !empty($post['date'])) { ?>
            <p class="main__date"><?= dateTag($post['date']) ?></p>
<?php } ?>
<?= $view['content'] ?>

          </div>
<?php renderPostNav($view['adjacent'], fn(array $item): string => postUrl($view['type'], $item)); ?>
        </article>
<?php renderLatestAside($view['type'] === SITE_LATEST_TYPE ? $view['posts'] : null); ?>
      </div>
    </main>
<?php
  renderFooter();
}
