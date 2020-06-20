import { __ } from '@wordpress/i18n';
import React from 'react';

class Message extends React.Component {
	handleDismiss = ( event ) => {
		event.preventDefault();

		this.props.dismiss( this.props.id, this.props.index );
	};

	/**
	 * Render.
	 */
	render() {
		const { notice } = this.props;
		const classes = [ 'notice', 'is-dismissable' ];

		classes.push( 'notice-' + notice.type );

		return (
			<div className={ classes.join( ' ' ) }>
				<p>{ notice.text }</p>
				<button className="notice-dismiss" onClick={ this.handleDismiss }>
					<span className="screen-reader-text">
						{ __( 'Dismiss this notice.', 'dark-matter' ) }
					</span>
				</button>
			</div>
		);
	}
}

export default Message;
