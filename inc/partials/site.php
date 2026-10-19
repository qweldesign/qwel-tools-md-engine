<?php
/**
 * site.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 全ページ共通のヘッダー・フッターを出力する
 * 中身 (サイト名, ナビ, SNS など) は config.php の設定から作る
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/helpers.php';

/**
 * <head> ~ ヘッダーまで出力
 * $page:
 *   title: ページのタイトル (<title> は「title | サイト名」. 省略するとサイト名だけ)
 *   full_title: <title> をそのまま指定する (トップページなど)
 *   description: description (規定で SITE_DESCRIPTION)
 *   path: このページのサイトのルートからのパス (canonical, og:url に使う)
 *   image: OGP 画像 (規定で SITE_OGP_IMAGE)
 *   type: og:type (規定で article)
 *   noindex: 検索結果に出さない (404 など)
 */
function render_header(array $page = []): void {
  $title = $page['full_title'] ?? (!empty($page['title']) ? $page['title'] . ' | ' . SITE_NAME : SITE_NAME);
  $description = ($page['description'] ?? '') ?: SITE_DESCRIPTION;
  $url = absolute_url($page['path'] ?? '');
  $image = absolute_url(($page['image'] ?? '') ?: SITE_OGP_IMAGE);
  ?>
<!DOCTYPE html>
<html lang="<?= e(SITE_LANG) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--
      Built with QWEL Markdown Engine (Simple CMS)
      A lightweight, file-based CMS that renders Markdown as HTML.
      Created by QWEL.DESIGN - https://qwel.design
      Source: https://github.com/qweldesign/qwel-tools-md-engine (MIT License)
    -->
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
<?php if (!empty($page['noindex'])) { ?>
    <meta name="robots" content="noindex">
<?php } else { ?>
    <link rel="canonical" href="<?= e($url) ?>">
<?php } ?>
    <meta property="og:type" content="<?= e($page['type'] ?? 'article') ?>">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($url) ?>">
    <meta property="og:image" content="<?= e($image) ?>">
    <meta name="twitter:card" content="summary_large_image">
<?php if (SITE_FONTS_URL) { ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e(SITE_FONTS_URL) ?>">
<?php } ?>
    <link rel="stylesheet" href="<?= path('style.css') ?>">
    <link rel="icon" href="<?= path('favicon.ico') ?>">
  </head>
  <body>
    <header class="header">
      <p id="logo" class="header__logo">
        <a href="<?= e(SITE_LOGO_LINK ?: path()) ?>">
<?php if (SITE_LOGO) { ?>
          <img src="<?= path(SITE_LOGO) ?>" alt="<?= e(SITE_NAME) ?>">
<?php } else { ?>
          <?= e(SITE_NAME) ?>
<?php } ?>
        </a>
      </p>
<?php if (SITE_NAV) { ?>
      <nav class="header__nav" aria-label="グローバルナビゲーション">
        <ul>
<?php foreach (SITE_NAV as $navPath => $label) { ?>
          <li><a href="<?= e(path($navPath)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
    </header>
<?php
}

// フッター ~ </html> まで出力
function render_footer(): void {
  ?>
    <footer id="footer" class="footer">
<?php if (SITE_FOOTER_LINKS) { ?>
      <nav class="footer__nav" aria-label="サイト内のページ">
        <ul>
<?php foreach (SITE_FOOTER_LINKS as $linkPath => $label) { ?>
          <li><a href="<?= e(path($linkPath)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
<?php if (SITE_SNS) { ?>
      <div class="footer__inner">
<?php foreach (SITE_SNS as $sns) { ?>
<?php $external = (bool)preg_match('#^https?://#', $sns['url']); ?>
        <a class="footer__item" href="<?= e(path($sns['url'])) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>>
<?php if (!empty($sns['icon'])) { ?>
          <svg class="icon is-md" width="36" height="36" aria-hidden="true">
            <use href="<?= path('icons.svg') ?>#<?= e($sns['icon']) ?>"></use>
          </svg>
<?php } ?>
          <span><?= e($sns['label']) ?></span>
        </a>
<?php } ?>
      </div>
<?php } ?>
      <small class="footer__copyright">&copy; <?= e(copyright_years()) ?> <?= e(SITE_OWNER) ?></small>
    </footer>
    <script src="<?= path('init.js') ?>" type="module"></script>
  </body>
</html>
<?php
}
