// jest テスト環境で window.wp.components を実コンポーネントでモックする
const { SelectControl, NoticeList, Placeholder } = require('@wordpress/components');

global.wp = {
	...(global.wp ?? {}),
	components: {
		SelectControl,
		NoticeList,
		Placeholder,
	},
};
