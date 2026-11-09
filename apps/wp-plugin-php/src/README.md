# Application と Repository の配置規約

## Application の分類

- [Application/Port](Application/Port): Infrastructure 実装前提の抽象（`interface` / `abstract class`）のみを置きます。具象クラスは置きません。
- [Application/Service](Application/Service): 具象の application service のみを置きます。
- [Application/Logging](Application/Logging): ログ基盤の抽象を置く例外ディレクトリです。`AppLogService` / `AppLogLevelRepository` / `ErrorLogWriter`（抽象）、`ValueObject`（`LogLevel`）を現状の配置のまま置きます。具象は Infrastructure 側に置き、`WpAppLogService` は [Infrastructure/WordPress/Logging](Infrastructure/WordPress/Logging)、`ErrorLogFormatter` / `ErrorLogWriterImpl` は [Infrastructure/Logging](Infrastructure/Logging) にあります。
- 役割の判別はディレクトリで行い、命名規則（`*Port` 接尾辞など）は導入しません。

## Repository のポート（インターフェース）

- 必要とする最上位層に置きます。
  - Domain から使われる → [Domain/Repository](Domain/Repository)
  - Domain から使われない（Application / Presentation のみが使う）→ [Application/Repository](Application/Repository)
  - 上位層（Domain / Application / Presentation）から参照されない（Infrastructure のみが使う）→ ポートを作らず、[Infrastructure](Infrastructure) の具象クラスを直接使います。
- `AppLogLevelRepository` の配置は「Application の分類」を参照してください。

## Repository のアダプタ（実装）

- [Infrastructure/WordPress/Database](Infrastructure/WordPress/Database) に置きます。
  - table-backed（テーブルアクセス）の実装は `Database/Repository`
  - option-backed（`wp_options`）の実装は `Database/OptionRepository`

## 注記

- 本規約の対象は Repository ポートです。検索条件オブジェクト（[Domain/Repository/SearchCondition](Domain/Repository/SearchCondition)）は対象外で、`SalesHistorySearchCondition` の配置は別途判断します。
