// editor.scss / style.scss などの *.scss ファイルを TypeScript から import するための
// ambient 宣言。TypeScript 6.0 で noUncheckedSideEffectImports がデフォルトで
// true になったため、サイドエフェクト import の型チェックを通す目的で定義する
// (issue #373 Phase 5-1)。
declare module '*.scss';
