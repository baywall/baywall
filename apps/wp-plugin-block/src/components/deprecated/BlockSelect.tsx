interface BlockSelectProps extends React.ComponentProps< 'select' > {}
interface BlockSelectOptionProps extends React.ComponentProps< 'option' > {}

/**
 * ブロックエディタで描画するセレクトコントロール
 * @param root0
 * @deprecated
 */
export const BlockSelect: React.FC< BlockSelectProps > = ( { ...props } ) => {
	return <select { ...props } />;
};

/**
 * ブロックエディタで描画するセレクトコントロールのオプション
 * @param root0
 * @deprecated
 */
export const BlockSelectOption: React.FC< BlockSelectOptionProps > = ( { ...props } ) => {
	return <option { ...props } />;
};
