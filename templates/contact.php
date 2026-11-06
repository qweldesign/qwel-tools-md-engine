<?php
/**
 * contact.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの 3 画面 (入力 → 確認 → 完了)
 * 項目は config.php の SITE_CONTACT_FIELDS から作る
 * 入力内容は sessionStorage で確認画面へ渡し, 確認画面から api/send.php へ送る (js/contact-form.js)
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 *
 * 使い方 (SITE_CONTACT の path と同じディレクトリ):
 *   index.php:         renderContact('form');
 *   confirm/index.php: renderContact('confirm');
 *   thanks/index.php:  renderContact('thanks');
 * 入力画面をトップページのセクションなどに置く場合は, SITE_CONTACT の formUrl を設定し, その場所で renderContactForm() を呼ぶ
 */

require_once dirname(__DIR__) . '/inc/ContactForm.php';
require_once dirname(__DIR__) . '/inc/ContentNavigation.php';
require_once __DIR__ . '/navigation.php';

// 各画面の URL ($step: '' (入力), 'confirm/', 'thanks/'). 入力画面は, formUrl があればその URL
function contactUrl(string $step = ''): string {
  if ($step === '' && SITE_CONTACT['formUrl']) return path(SITE_CONTACT['formUrl']);
  return path(SITE_CONTACT['path'] . $step);
}

// $step: form (入力), confirm (確認), thanks (完了)
function renderContact(string $step): void {
  // 入力画面を別の場所に置いている場合は, そこへ転送する
  if ($step === 'form' && SITE_CONTACT['formUrl']) {
    header('Location: ' . contactUrl(), true, 301);
    exit;
  }

  $title = SITE_CONTACT['title'];
  $stepTitle = ['confirm' => '入力内容の確認', 'thanks' => '送信完了'][$step] ?? null;

  renderHeader([
    'title'       => $stepTitle ?? $title,
    'description' => strip_tags(SITE_CONTACT['lead']),
    'noindex'     => $stepTitle !== null,
    'path'        => SITE_CONTACT['path'] . ($stepTitle ? $step . '/' : ''),
  ]);

  $crumbs = [...SITE_BREADCRUMB, ['label' => $title, 'path' => SITE_CONTACT['formUrl'] ?: SITE_CONTACT['path']]];
  if ($stepTitle) $crumbs[] = ['label' => $stepTitle];
?>
    <main class="main">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs($crumbs)); ?>
      <h1 class="main__title"><?= e($stepTitle ?? $title) ?></h1>
<?php
  match ($step) {
    'confirm' => renderContactConfirm(),
    'thanks'  => renderContactThanks(),
    default   => renderContactForm(),
  };
?>
    </main>
<?php
  renderFooter();
}

// 入力画面 (トップページのセクションなどからも呼べる). lead は HTML をそのまま出す
function renderContactForm(): void {
  $honeypot = ContactForm::HONEYPOT;
?>
<?php if (SITE_CONTACT['lead']) { ?>
      <p><?= SITE_CONTACT['lead'] ?></p>
<?php } ?>
      <form class="form" data-contact-form data-confirm-url="<?= e(contactUrl('confirm/')) ?>">
        <table>
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php renderContactField($name, $field); ?>
<?php } ?>
        </table>
        <div class="form__honeypot" aria-hidden="true">
          <label for="contact-<?= e($honeypot) ?>">この欄は空のままにしてください</label>
          <input id="contact-<?= e($honeypot) ?>" type="text" name="<?= e($honeypot) ?>" tabindex="-1" autocomplete="off" data-contact-honeypot>
        </div>
        <input type="submit" value="確認">
      </form>
<?php
}

// 入力画面の 1 項目 (表の 1 行)
function renderContactField(string $name, array $field): void {
  $type = $field['type'] ?? 'text';
  $id = 'contact-' . $name;
  $required = !empty($field['required']);
  $label = e($field['label'] ?? $name) . ($required ? '<span class="is-required">*必須</span>' : '');
  // 入力欄に付ける属性
  $attrs = ' name="' . e($name) . '"';
  if ($required && $type !== 'checkbox') $attrs .= ' required';
  if (!empty($field['autocomplete'])) $attrs .= ' autocomplete="' . e($field['autocomplete']) . '"';
  if (!empty($field['placeholder'])) $attrs .= ' placeholder="' . e($field['placeholder']) . '"';
?>
          <tr>
<?php if ($type === 'radio' || $type === 'checkbox') { ?>
            <th><span id="<?= e($id) ?>-label"><?= $label ?></span></th>
            <td>
              <ul class="form__choices" role="group" aria-labelledby="<?= e($id) ?>-label">
<?php foreach ($field['options'] ?? [] as $index => $option) { ?>
                <li><label><input type="<?= e($type) ?>"<?= $attrs ?> value="<?= e($option) ?>"<?= $type === 'radio' && $index === 0 && $required ? ' checked' : '' ?>> <?= e($option) ?></label></li>
<?php } ?>
              </ul>
            </td>
<?php } else { ?>
            <th><label for="<?= e($id) ?>"><?= $label ?></label></th>
            <td>
<?php if ($type === 'textarea') { ?>
              <textarea id="<?= e($id) ?>"<?= $attrs ?> rows="<?= (int)($field['rows'] ?? 8) ?>"></textarea>
<?php } elseif ($type === 'select') { ?>
              <select id="<?= e($id) ?>"<?= $attrs ?>>
<?php foreach ($field['options'] ?? [] as $option) { ?>
                <option value="<?= e($option) ?>"><?= e($option) ?></option>
<?php } ?>
              </select>
<?php } else { ?>
              <input id="<?= e($id) ?>" type="<?= e($type) ?>"<?= $attrs ?>>
<?php } ?>
            </td>
<?php } ?>
          </tr>
<?php
}

// 確認画面 (値は js/contact-form.js が sessionStorage から入れる)
function renderContactConfirm(): void {
?>
      <div class="confirm" data-contact-confirm data-form-url="<?= e(contactUrl()) ?>" data-thanks-url="<?= e(contactUrl('thanks/')) ?>" data-send-url="<?= e(path('api/send.php')) ?>">
        <div hidden data-contact-filled>
          <p>以下の内容で送信します。よろしければ「送信する」を押してください。</p>
          <table>
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
            <tr>
              <th><?= e($field['label'] ?? $name) ?></th>
              <td data-contact-value="<?= e($name) ?>"></td>
            </tr>
<?php } ?>
          </table>
          <p class="confirm__message" role="alert" hidden data-contact-message></p>
          <input type="submit" value="送信する" data-contact-send>
          <input type="button" value="入力画面に戻る" data-contact-back>
        </div>
        <div hidden data-contact-empty>
          <p>入力内容が見つかりませんでした。お手数ですが、もう一度ご入力ください。</p>
          <p><a href="<?= e(contactUrl()) ?>"><?= e(SITE_CONTACT['title']) ?>へ</a></p>
        </div>
        <noscript><p>お問い合わせフォームのご利用には JavaScript を有効にしてください。</p></noscript>
      </div>
<?php
}

// 完了画面
function renderContactThanks(): void {
?>
      <div class="thanks">
        <p>お問い合わせありがとうございました。<br>ご入力いただいたメールアドレスに、確認のメールをお送りしました。</p>
        <p><a href="<?= e(path()) ?>">トップへ戻る</a></p>
      </div>
<?php
}
