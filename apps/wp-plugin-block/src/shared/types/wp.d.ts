// WordPressが実行時に提供するグローバル wp オブジェクトの型宣言
declare interface Window {
	wp: {
		data: {
			dispatch: (storeNameOrDescriptor: string) => Record<string, (...args: any[]) => any>;
		};
		blocks: {
			registerBlockType: (...args: any[]) => any;
		};
		blockEditor: {
			useBlockProps: ((...args: any[]) => Record<string, unknown>) & {
				save: (props?: Record<string, unknown>) => Record<string, unknown>;
			};
		};
		components: {
			Placeholder: React.ComponentType<any>;
			SelectControl: React.ComponentType<any>;
			NoticeList: React.ComponentType<any>;
		};
	};
}
