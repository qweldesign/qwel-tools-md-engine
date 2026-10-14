<?php
/**
 * posts.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 投稿タイプの個別記事を出力する共通テンプレート
 * 個別記事: ?slug=xxx (SITE_PRETTY_URLS なら xxx/)
 * このサイトでは投稿タイプの一覧ページを持たない (一覧の URL はトップページへ転送する)
 *
 * 使い方 (投稿タイプのディレクトリの index.php):
 *   require_once __DIR__ . '/inc/templates/posts.php';
 *   render_posts('blog');
 */

require_once __DIR__ . '/postList.php';

/**
 * $type: 投稿タイプ (config.php の SITE_POST_TYPES のキー)
 * $options:
 *   latest: 個別記事の横に並べる最新記事の件数 (規定で SITE_LATEST_COUNT. 0 で出さない)
 *   adjacent: 個別記事の下に前後の記事へのリンクを出すか (規定で true)
 */
function render_posts(string $type, array $options = []): void {
  $latest = $options['latest'] ?? SITE_LATEST_COUNT;
  $cms = content_engine($type);

  // 一覧ページ (/blog/, /blog/page/2/ など) は持たないので, トップページへ転送する
  if (!$cms->is_single()) {
    header('Location: ' . path(), true, 301);
    exit;
  }

  // 記事が見つからない場合は 404
  $notFound = !$cms->is_found();
  if ($notFound) {
    http_response_code(404);
    $page = [
      'title'   => 'ページが見つかりません',
      'path'    => type_path($type),
      'noindex' => true,
    ];
  } else {
    $page = [
      'title'       => $cms->get_title(),
      'description' => $cms->get_meta('summary'),
      'path'        => post_path($type, ['slug' => $cms->get_meta('slug')]),
      'image'       => $cms->get_meta('img'),
    ];
  }

  render_header($page);
?>
    <main id="main" class="main">
<?php if ($notFound) { ?>
      <?= (new ContentNavigation(['parents' => site_breadcrumb_parents(), 'subdirLabel' => null]))->breadcrumb(['title' => 'ページが見つかりません']) ?>
<?php } else { ?>
      <?= $cms->get_breadcrumb() ?>
<?php } ?>
<?php render_site_heading(); ?>
<?php if ($latest > 0) { ?>
      <div class="main__row">
        <article class="main__article">
<?php } else { ?>
      <div>
        <article>
<?php } ?>
<?php if ($notFound) { ?>
          <div class="entry">
            <h2 class="entry__title">ページが見つかりません</h2>
            <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
            <p><a href="<?= e(path()) ?>">トップページへ</a></p>
          </div>
<?php } else { ?>
          <div class="entry">
            <h2 class="entry__title"><?= e($cms->get_title()) ?></h2>
<?php if (post_type($type)['show_date'] && $cms->get_meta('date')) { ?>
            <p class="main__date"><?= date_tag($cms->get_meta('date')) ?></p>
<?php } ?>
<?= $cms->get_content() ?>
          </div>
<?php if ($options['adjacent'] ?? true) { ?>
<?php $adjacent = $cms->get_adjacent(); ?>
          <nav class="postNav" aria-label="前後の記事">
<?php if ($adjacent['prev']) { ?>
            <a class="postNav__link is-prev" href="<?= e(post_url($type, $adjacent['prev'])) ?>">
              <span class="postNav__label">新しい記事</span>
              <span class="postNav__title"><?= e($adjacent['prev']['title'] ?? '') ?></span>
            </a>
<?php } ?>
<?php if ($adjacent['next']) { ?>
            <a class="postNav__link is-next" href="<?= e(post_url($type, $adjacent['next'])) ?>">
              <span class="postNav__label">前の記事</span>
              <span class="postNav__title"><?= e($adjacent['next']['title'] ?? '') ?></span>
            </a>
<?php } ?>
          </nav>
<?php } ?>
<?php } ?>
        </article>
<?php if ($latest > 0) { ?>
        <aside class="main__aside">
          <h2>最新記事</h2>
<?php render_post_index(content_engine(SITE_LATEST_TYPE, ['slug' => null, 'page' => 1])->get_posts(1, $latest), SITE_LATEST_TYPE); ?>
        </aside>
<?php } ?>
      </div>
    </main>
<?php
  render_footer();
}
