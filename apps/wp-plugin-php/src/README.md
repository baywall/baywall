# Application と Repository の配置規約

## Application の分類

- [Application/Port](Application/Port): Infrastructure 実装前提の抽象（`interface` / `abstract class`）のみを置きます。具象クラスは置きません。
- [Application/Service](Application/Service): 具象の application service のみを置きます。
- 役割の判別はディレクトリで行い、命名規則（`*Port` 接尾辞など）は導入しません。

## Repository のポート（インターフェース）

- 必要とする最上位層に置きます。
  - Domain から使われる → [Domain/Repository](Domain/Repository)
  - Domain から使われない（Application / Presentation のみが使う）→ [Application/Repository](Application/Repository)
  - 補足: ログ基盤の `LogLevelRepository` は [Application/Logging](Application/Logging) に置きます。

## Repository のアダプタ（実装）

- [Infrastructure/WordPress/Database](Infrastructure/WordPress/Database) に置きます。
  - table-backed（テーブルアクセス）の実装は `Database/Repository`
  - option-backed（`wp_options`）の実装は `Database/OptionRepository`

## 注記

- 本規約の対象は Repository ポートです。検索条件オブジェクト（[Domain/Repository/SearchCondition](Domain/Repository/SearchCondition)）は対象外で、`SalesHistorySearchCondition` の配置は別途判断します。
