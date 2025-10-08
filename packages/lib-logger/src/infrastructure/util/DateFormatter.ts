export class DateFormatter {
	/**
	 * DateオブジェクトをUTCのSQL("yyyy-MM-dd HH:mm:ss")形式の文字列に変換して返します
	 *
	 * @param date
	 * @return "yyyy-MM-dd HH:mm:ss"形式の文字列
	 */
	toSqlUtc( date: Date ): string {
		const pad = ( n: number ) => String( n ).padStart( 2, '0' );
		return (
			[ date.getUTCFullYear(), pad( date.getUTCMonth() + 1 ), pad( date.getUTCDate() ) ].join( '-' ) +
			' ' +
			[ pad( date.getUTCHours() ), pad( date.getUTCMinutes() ), pad( date.getUTCSeconds() ) ].join( ':' )
		);
	}
}
