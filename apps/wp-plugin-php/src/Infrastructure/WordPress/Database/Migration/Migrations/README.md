## Migrations
このディレクトリには`WordPress`データベースのマイグレーションに関連するクラスが含まれています。

この`README.md`ファイルと同一ディレクトリにある`php`ファイルがマイグレーションを表します。
マイグレーションファイル以外はこのディレクトリに配置しないでください。(サブディレクトリは使用可)

### CHECK 制約の正規表現式
正規表現で値域を固定する列（識別子・ハッシュ・署名・Base62 の nonce）は`CONVERT(<col> USING utf8mb4) COLLATE utf8mb4_bin REGEXP '<pattern>'`で制約を書きます。
この形は次の理由で決まっており、いずれの要素も省けません。

- `BINARY`演算子をパターンリテラルに付ける書き方（`REGEXP BINARY '<pattern>'`）は使えません。
  MySQL 8.0.22以降は正規表現関数へバイナリ文字列を渡せず、`CREATE TABLE`が`ERROR 3995`で失敗します。
  この失敗はMariaDBでは起きないため、CI（`mariadb:lts`）では検知できません。
- `COLLATE utf8mb4_bin`が必要です。テーブル既定の照合順序が`_ci`のままだと大小文字を区別できず、
  小文字ULIDがそのまま通り、制約が静かに無効化されます。
- `CONVERT(<col> USING utf8mb4)`が必要です。`COLLATE utf8mb4_bin`単体では、テーブル既定が
  utf8mb3 / latin1のサイトで`ERROR 1253`になります。
- `REGEXP_LIKE()`はMariaDBに存在しないため使えません。

### 複数の列に依存する CHECK 制約
判別子の列と書式を固定する列を組にして値域を決める場合（例: `server_signer`の`private_key_enc_type`と`private_key`）は、`CASE`を使わず`AND` / `OR`で書きます。
`CASE`は既存の制約に前例が無く、`AND` / `OR`の方が SQL の標準的な述語で短いからです。

- 判別子の値域は`= <値>`の条件で表し、値域専用の`IN (...)`制約を併記しません。同じ列に値域制約と書式制約が並ぶと DDL から実際の値域が読み取れなくなります（`IN (1, 2)`は`2`を許すと読めるのに、書式条件は`2`を拒否する、という状態になります）。
- 分岐を増やす場合は`OR`で並べ、各分岐が自分の判別子の値と書式条件を持ちます（`(<判別子> = <値> AND <その列の書式>) OR (<判別子> = <値> AND <その列の書式>)`）。値域は現れる`= <値>`の集合として読み取ります。分岐ごとに書式条件を持たせると、後から分岐を足すときに既存の分岐を書き換えずに済みます。
- 分岐が 1 つだけの場合は`OR`を書かず`AND`のままにします（例: `` CHECK (`private_key_enc_type` = 1 AND <平文書式>) ``）。
- `$`アンカーは末尾改行 1 文字を許容します（MySQL 8 では加えて`\r` / `\v` / `\f`も許容します）。既存の全正規表現 CHECK に共通する性質で、`CHAR_LENGTH()`の併記で閉じる案は「正規表現式は`CONVERT(<col> USING utf8mb4) COLLATE utf8mb4_bin REGEXP '<pattern>'`の形に統一する」という本節の規約と衝突するため採用しません。対処しない既知の制約です（値は`base64_encode()`の戻り値や将来の暗号化出力に限られるため実務上の影響は小さい）。

### 文字列型の列の幅
値域が固定の文字列型の列は`varchar(191)`で統一します。`wp_options.option_name`と同じ WordPress の慣習であり、utf8mb4 / utf8mb3 / latin1 のどの charset でも索引長の上限に収まる安全側の値だからです。
値域が固定でない列はこの統一の対象外です。型幅が 191 でないのは次の列です（URL の`chain.rpc_url`・`chain.block_explorer_url`は`varchar(512)`、列挙値の`log.level`・`log.category`は`varchar(20)`／`varchar(50)`、本文の`seller.signing_message`は`text`、`log.message`は`mediumtext`、`paid_content.paid_content`は`longtext`）。`chain.name`や各テーブルの`symbol`系の列も値域が固定ではありませんが、`varchar(191)`であるためここでは挙げていません。
値域が固定の列でも`char(n)`は採用しません。理由は次の 2 つです。(1) `erc4361_nonce`（Base62 の 8〜11 文字）のように定長ではない列があるため。(2) 値域の情報源を CHECK 制約の 1 箇所に保つため（`char(n)`を併用すると長さが型幅と CHECK の量化子の 2 箇所に分かれ、どちらか片方だけ直すと不整合が生じる）。加えて、CHAR 型は右側を半角スペースで埋めて格納します。`PAD_CHAR_TO_FULL_LENGTH`が有効なサイトでは末尾の空白が保持されるため、DB から読み直した nonce で ERC-4361 メッセージを再構築する署名検証（ログイン）が常に失敗します。

### ファイル命名規則
ファイル名の昇順で実行されるため、マイグレーションファイル名は必ず`V{YYYYMMDD}`(`V` + 作成日)から始めてください。(仕様)
また、ファイル名にマイグレーションの内容を簡潔に説明する文字列を含めてください。

- 例1: `V20251106_CreateAppContractTable.php`
  - `20251106`: マイグレーション作成日(YYYYMMDD形式)
  - `CreateAppContractTable`: マイグレーション内容の説明
- 例2: `V20251106_020_CreateAppContractTable.php`
  - `20251106`: マイグレーション作成日(YYYYMMDD形式)
  - `020`: 同日に複数のマイグレーションがある場合のソート用の数値(省略可)
  - `CreateAppContractTable`: マイグレーション内容の説明
