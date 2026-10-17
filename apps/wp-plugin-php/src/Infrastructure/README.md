# Infrastructure

外部 IO（DB / WP 関数 / ネットワーク）を担う層です。

上位層（Domain / Application）からは、Domain / Application で定義したポート（インターフェース）経由でのみ参照してください。
