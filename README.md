# QWEL Markdown Engine (Simple CMS)

Markdown ファイルを記事として読み込み、HTML に変換して表示するためのシンプルな PHP エンジン。  
データベース不要で、作品集やブログなどの**軽量なサイト向けの CMS として利用可能**。

▶ Sample DEMO: [https://tools.qwel.design/simple-cms/]

---

## 概要・機能 | Overview & Features

- Markdown を HTML に変換して表示
- データベース不要、Markdown ファイルベースで管理
- パンくずリストやページネーションを自動生成
- 記事取得のための API 有り
- PHP のみで動作, 軽量・シンプル

---

## 目的・背景 | Purpose & Background

- 環境構築を手軽に行える, 軽量な CMS が欲しかった
- UIの実装・運用・保守に伴うコストとセキュリティリスクを抑えたかった
- クライアントから文章と画像を提供してもらい, 更新自体はエンジニアが行う運用方針でOK
- Markdown をそのまま運用することで, Git 管理による保守が可能な環境を試してみたかった
- 外部依存は最小限 (Markdown パーサーのみ) に抑え, シンプルな設計を目指す

---

## 設計・実装 | Design & Implementation

- 構成をなるべく小さく, 理解し易く保つ
- 記事管理は Markdown + frontmatter を基本とする
- Loader (読み込み) と Navigation (UI補助) を責務分離
- CMS というより「コンテンツエンジン」として扱う
- 機能よりも可搬性を重視 (ベーシック認証や記事編集画面は作らない)

---

## ライセンス | License

MIT License

このプロジェクトは, お知らせ等簡易ブログシステムの用途として自由に使用できることを目的としています。  
This project is designed to be freely used as a lightweight blog system for announcements.  

詳しくは LICENSE ファイルをご覧ください。  
See the LICENSE file for details.  

---

## 制作者 | Author

[QWEL.DESIGN](https://qwel.design)  
福井を拠点に活動するフロントエンド開発者  
Front-end developer based in Fukui, Japan  
