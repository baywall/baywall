import { BlockEditProps } from '../../types/BlockEditProps';
import { createContext } from 'react';
import { WidgetAttributes } from '../../types/WidgetAttributes';

type BlockEditPropsContextType = BlockEditProps<WidgetAttributes>;

export const BlockEditPropsContext = createContext<BlockEditPropsContextType | undefined>(undefined);

type BlockEditPropsProviderProps = {
	blockEditProps: BlockEditProps<WidgetAttributes>;
	children: React.ReactNode;
};

/**
 * ブロックのプロパティを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 * @param root0.blockEditProps
 */
export const BlockEditPropsProvider = ({ blockEditProps, children }: BlockEditPropsProviderProps) => {
	return <BlockEditPropsContext.Provider value={blockEditProps}>{children}</BlockEditPropsContext.Provider>;
};
