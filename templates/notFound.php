<?php
/**
 * notFound.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 見つからないページ
 */

require_once __DIR__ . '/postList.php';

/**
 * $view:
 *   posts: 投稿タイプの全記事 (最新記事などに使う. inc/404.php から呼ばれたときは空)
 *   crumbs: パンくず
 *   backUrl, backLabel: 戻るリンク (このサイトは一覧ページを持たないので使わず, トップページへ戻す)
 */
function renderNotFound(array $view): void {
  $title = 'ページが見つかりません';
  renderHeader(['title' => $title, 'noindex' => true]);
?>
    <main class="main">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs([...SITE_BREADCRUMB, ['label' => $title]])); ?>
<?php renderSiteHeading(); ?>
      <div class="main__row">
        <article class="main__article">
          <div class="entry">
            <h2><?= e($title) ?></h2>
            <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
            <p><a href="<?= e(path()) ?>">トップページへ</a></p>
          </div>
        </article>
<?php renderLatestAside(); ?>
      </div>
    </main>
<?php
  renderFooter();
}
