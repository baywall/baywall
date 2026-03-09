#!/usr/bin/env bash
set -euo pipefail

LANGUAGES=(
	# "en"
	"ja"
)
I18N_WORK_DIR="i18n"
OUTPUT_DIR="languages"
TEXT_DOMAIN="baywall"

i18n() {
	# ディレクトリ移動があるためサブシェルで実行
	(
		echo "[F1ADE66C] 📦 Building i18n files..."

		script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
		cd "$script_dir/../.."

		# ディレクトリの作成
		mkdir -p "$I18N_WORK_DIR"
		mkdir -p "$OUTPUT_DIR"

		# ①.potファイルの生成
		# WordPressのi18nツールを使用して、翻訳テンプレートファイル(.pot)を生成します。
		wp i18n make-pot . "$I18N_WORK_DIR/$TEXT_DOMAIN.pot" --domain=$TEXT_DOMAIN --include=src --merge="$I18N_WORK_DIR/$TEXT_DOMAIN.pot"

		# ②.poファイルの生成/更新
		# 各言語の翻訳ファイル(.po)が存在しない場合は.potファイルをコピーして作成します。
		# 存在する場合は msgmerge を使用して既存の翻訳ファイルを更新します。
		for lang in "${LANGUAGES[@]}"; do
			po_file="$I18N_WORK_DIR/$TEXT_DOMAIN-$lang.po"
			if [ ! -f "$po_file" ]; then
				cp "$I18N_WORK_DIR/$TEXT_DOMAIN.pot" "$po_file"
				echo "[31876BB2] 📄 Created $po_file from $TEXT_DOMAIN.pot"
			else
				# -N は --no-fuzzy-matching の短縮形（似ている翻訳を検出して自動的に翻訳する機能を無効化）
				msgmerge --update --backup=none --silent -N "$po_file" "$I18N_WORK_DIR/$TEXT_DOMAIN.pot"
				echo "[BC0A8882] 🔄 Updated $po_file with $TEXT_DOMAIN.pot"
			fi
		done

		# ③.moファイルの生成
		# .poファイルからコンパイルされたバイナリ形式の翻訳ファイル(.mo)を生成します。
		for lang in "${LANGUAGES[@]}"; do
			po_file="$I18N_WORK_DIR/$TEXT_DOMAIN-$lang.po"
			mo_file="$OUTPUT_DIR/$TEXT_DOMAIN-$lang.mo"
			msgfmt "$po_file" -o "$mo_file"
			echo "[AC4D52ED] ✅ Compiled $po_file to $mo_file"
		done

		echo "[8A52A810] 🎉 i18n files built successfully!"
	)
}

main() {
	i18n
}
main