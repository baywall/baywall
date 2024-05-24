export const getPostIdFromDom = (): number | null => {
	const postIdElement = document.getElementById( 'post_ID' );

	if ( ! postIdElement ) {
		// `post_ID`のIDを持つ要素が取得できない場合は、`window.name`から取得して返す。
		return getPostIdFromWindowName();
	}

	const val = ( postIdElement as HTMLInputElement ).value;
	const result = Number( val );
	if ( isNaN( result ) || ! postIdElement.hasAttribute( 'value' ) ) {
		console.error( `val: ${ JSON.stringify( val ) }` );
		throw new Error( '{D636C4B1-CE93-45CC-AA65-69FFCD578CAC}' );
	}

	return result;
};

const getPostIdFromWindowName = (): number | null => {
	const name = ( window as any ).name;
	if ( typeof name !== 'string' ) {
		return null;
	}

	if ( name.startsWith( 'wp-preview-' ) ) {
		return Number( name.replace( 'wp-preview-', '' ) );
	}

	return null;
};
