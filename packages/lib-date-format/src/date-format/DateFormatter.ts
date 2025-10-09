export class DateFormatter {
	/** `yyyy`などのフォーマットをキーとして、それに対するresolverのマップ */
	private formatMap: Map< string, ( date: Date ) => string > | undefined;

	/**
	 * UTC時間でフォーマットされた日付を返します
	 * @param date
	 * @param format
	 */
	public format( date: Date, format: string ): string {
		if ( ! this.formatMap ) {
			this.formatMap = new Map< string, ( date: Date ) => string >( [
				[ 'yyyy', ( d: Date ) => String( d.getUTCFullYear() ).padStart( 4, '0' ) ],
				[ 'MM', ( d: Date ) => String( d.getUTCMonth() + 1 ).padStart( 2, '0' ) ],
				[ 'dd', ( d: Date ) => String( d.getUTCDate() ).padStart( 2, '0' ) ],
				[ 'HH', ( d: Date ) => String( d.getUTCHours() ).padStart( 2, '0' ) ],
				[ 'mm', ( d: Date ) => String( d.getUTCMinutes() ).padStart( 2, '0' ) ],
				[ 'ss', ( d: Date ) => String( d.getUTCSeconds() ).padStart( 2, '0' ) ],
				[ 'SSS', ( d: Date ) => String( d.getUTCMilliseconds() ).padStart( 3, '0' ) ],
			] );
		}

		this.formatMap.forEach( ( resolver, key ) => {
			format = format.replace( new RegExp( key, 'g' ), resolver( date ) );
		} );

		return format;
	}
}
