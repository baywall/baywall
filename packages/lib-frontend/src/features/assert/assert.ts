type AssertFn = (condition: unknown, message: string) => asserts condition;

// アサーション関数は呼び出し元で型の絞り込みが効くよう、明示的な型注釈で宣言する必要がある（TS2775）。
export const assert: AssertFn = (condition, message) => {
	if (!condition) {
		throw new Error(message);
	}
};
