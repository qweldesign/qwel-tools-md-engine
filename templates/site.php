<?php
/**
 * site.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 全ページ共通のヘッダーとフッター
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/inc/helpers.php';

/**
 * <head> からヘッダーまでを出力する
 * $page:
 *   title: ページのタイトル (<title> は「title | サイト名」. 省略するとトップページの SITE_TITLE)
 *   description: description (既定で SITE_DESCRIPTION)
 *   noindex: 検索結果に出さない (404 など). canonical は出さない
 *   path: このページのサイトのルートからのパス (canonical, og:url に使う. 省略すると出さない)
 *   image: OGP 画像 (既定で SITE_OGP_IMAGE. ファイルがなければ既定の画像にする)
 *   imageAlt: OGP 画像の代替テキスト (既定で, 記事の画像ならページのタイトル, 既定の画像なら SITE_OGP_IMAGE_ALT)
 *   type: og:type (既定で article)
 *   head: </head> の直前に出力する HTML (構造化データなど. エスケープしない)
 */
function renderHeader(array $page = []): void {
  $title = !empty($page['title']) ? $page['title'] . ' | ' . SITE_NAME : (SITE_TITLE ?: SITE_NAME);
  $description = ($page['description'] ?? '') ?: SITE_DESCRIPTION;
  $url = isset($page['path']) ? absoluteUrl($page['path']) : null;
  $image = ogImage($page['image'] ?? '');
  $imageAlt = $page['imageAlt'] ?? ($image && !$image['isDefault'] ? ($page['title'] ?? $title) : (defined('SITE_OGP_IMAGE_ALT') ? SITE_OGP_IMAGE_ALT : ''));
  $locale = defined('SITE_OG_LOCALE') ? SITE_OG_LOCALE : '';
?>
<!DOCTYPE html>
<html lang="<?= e(SITE_LANG) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--
      Built with QWEL Markdown Engine (Simple CMS) v<?= e(SITE_ENGINE_VERSION) ?>

      A lightweight, file-based CMS that renders Markdown as HTML.
      Created by QWEL.DESIGN - https://qwel.design
      Source: https://github.com/qweldesign/qwel-tools-md-engine (MIT License)
    -->
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
<?php if (!empty($page['noindex'])) { ?>
    <meta name="robots" content="noindex">
<?php } elseif ($url) { ?>
    <link rel="canonical" href="<?= e($url) ?>">
<?php } ?>
    <meta property="og:type" content="<?= e($page['type'] ?? 'article') ?>">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
<?php if ($url) { ?>
    <meta property="og:url" content="<?= e($url) ?>">
<?php } ?>
<?php if ($locale) { ?>
    <meta property="og:locale" content="<?= e($locale) ?>">
<?php } ?>
<?php if ($image) { ?>
    <meta property="og:image" content="<?= e($image['url']) ?>">
<?php if ($image['width']) { ?>
    <meta property="og:image:width" content="<?= $image['width'] ?>">
    <meta property="og:image:height" content="<?= $image['height'] ?>">
<?php } ?>
<?php if ($imageAlt !== '') { ?>
    <meta property="og:image:alt" content="<?= e($imageAlt) ?>">
<?php } ?>
<?php } ?>
    <meta name="twitter:card" content="<?= $image ? 'summary_large_image' : 'summary' ?>">
<?php if (SITE_FONTS_URL) { ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e(SITE_FONTS_URL) ?>">
<?php } ?>
    <link rel="stylesheet" href="<?= e(path('style.css')) ?>">
<?php if (siteFileExists('assets/favicon.ico')) { ?>
    <link rel="icon" href="<?= e(path('assets/favicon.ico')) ?>" sizes="32x32">
<?php } ?>
<?php if (siteFileExists('assets/favicon.svg')) { ?>
    <link rel="icon" href="<?= e(path('assets/favicon.svg')) ?>" type="image/svg+xml">
<?php } ?>
<?php if (siteFileExists('assets/apple-touch-icon.png')) { ?>
    <link rel="apple-touch-icon" href="<?= e(path('assets/apple-touch-icon.png')) ?>">
<?php } ?>
<?php if (SITE_GA_ID) { ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(SITE_GA_ID) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e(SITE_GA_ID) ?>');
    </script>
<?php } ?>
<?= $page['head'] ?? '' ?>
  </head>
  <body>
    <header class="header">
      <p class="header__logo">
        <a href="<?= e(SITE_LOGO_LINK ?: path()) ?>">
<?php if (SITE_LOGO) { ?>
          <img src="<?= e(path(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME) ?>">
<?php } else { ?>
          <?= e(SITE_NAME) ?>

<?php } ?>
        </a>
      </p>
<?php if (SITE_NAV) { ?>
      <nav class="header__nav" aria-label="メインメニュー">
        <ul>
<?php foreach (SITE_NAV as $href => $label) { ?>
          <li><a href="<?= e(path((string)$href)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
    </header>
<?php
}

// フッターから </html> までを出力する
function renderFooter(): void {
?>
    <footer id="footer" class="footer">
<?php if (SITE_FOOTER_LINKS) { ?>
      <nav class="footer__nav" aria-label="サイト内のリンク">
        <ul>
<?php foreach (SITE_FOOTER_LINKS as $href => $label) { ?>
          <li><a href="<?= e(path((string)$href)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
      <div class="footer__inner">
<?php foreach (SITE_SNS as $sns) { ?>
        <a class="footer__item" href="<?= e(path($sns['url'])) ?>"<?= isExternal($sns['url']) ? ' target="_blank" rel="noopener"' : '' ?>>
<?php if (!empty($sns['icon'])) { ?>
          <svg class="icon is-md" width="36" height="36" aria-hidden="true">
            <use href="<?= e(path('assets/icons.svg')) ?>#<?= e($sns['icon']) ?>"></use>
          </svg>
<?php } ?>
          <span><?= e($sns['label']) ?></span>
        </a>
<?php } ?>
        <small class="footer__copyright">&copy; <?= e(copyrightYears()) ?> <?= e(SITE_OWNER) ?></small>
      </div>
    </footer>
    <script src="<?= e(path('init.js')) ?>" type="module"></script>
  </body>
</html>
<?php
}
