<?php
/**
 * お問い合わせフォーム (入力画面: /contact/)
 * 項目は config.php の SITE_CONTACT_FIELDS で設定する
 */
require_once dirname(__DIR__) . '/inc/templates/contact.php';

render_contact('form');
