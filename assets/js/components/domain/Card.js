/* eslint-disable camelcase */

/**
 * WordPress Dependencies.
 */
import { compose } from '@wordpress/compose';
import { withDispatch } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { ToggleControl } from '../controls/ToggleControl';

/**
 * Card component.
 *
 * @param {Object} props Props.
 * @return {JSX.Element} Card JSX.
 */
class Card extends Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Props for Card.
	 */
	constructor( props ) {
		super( props );

		this.handleRemove = this.handleRemove.bind( this );
		this.handlePrimary = this.handlePrimary.bind( this );
	}

	/**
	 * Handle for toggling the Primary checkbox.
	 *
	 * @param {MouseEvent} e Event details.
	 */
	handleRemove( e ) {
		e.preventDefault();

		const { removeDomain, domain } = this.props;

		// eslint-disable-next-line no-alert
		if ( window.confirm( __( 'Are you sure you wish to remove this domain?', 'darkmatterplugin' ) ) ) {
			removeDomain( domain );
		}
	}

	/**
	 * Handle for toggling the Primary checkbox.
	 *
	 * @param {MouseEvent} e Event details.
	 */
	handlePrimary( e ) {
		const { domain } = this.props;
		console.log( domain, e ); // eslint-disable-line
	}

	/**
	 * Render the Card for a single domain.
	 *
	 * @return {JSX.Element} Card JSX.
	 */
	render() {
		const { domain, is_active, is_https, is_primary, type } = this.props;

		const types = {
			1: __( 'Main', 'darkmatterplugin' ),
			2: __( 'Media', 'darkmatterplugin' ),
		};

		const typeDisplay = is_primary
			? __( 'Primary', 'darkmatterplugin' )
			: types[ type ]
		;

		return (
			<div className="dmp__domain-card">
				<h2>{ domain }</h2>
				<div className="dmp__control-info">
					<span>{ __( 'Type: ', 'darkmatterplugin' ) }</span>
					<span>{ typeDisplay }</span>
				</div>
				<div className="dmp__control-info">
					<span>{ __( 'Protocol: ', 'darkmatterplugin' ) }</span>
					<span className={ is_https ? 'success' : 'warning' }>{ is_https ? __( 'https://', 'darkmatterplugin' ) : __( 'http://', 'darkmatterplugin' ) }</span>
				</div>
				<ToggleControl
					checked={ is_primary }
					label={ __( 'Primary', 'darkmatterplugin' ) }
					onChange={ this.handlePrimary }
				/>
				<ToggleControl checked={ is_active } label={ __( 'Active', 'darkmatterplugin' ) } onChange={ ( e ) => {
					console.log( e ); // eslint-disable-line
				} } />
				<div className="dmp__domain-card__actions">
					<button
						className="components-button is-secondary is-destructive"
						onClick={ this.handleRemove }
					>
						{ __( 'Remove', 'darkmatterplugin' ) }
					</button>
				</div>
			</div>
		);
	}
}

export default compose( [
	withDispatch( ( dispatch ) => {
		return {
			removeDomain: dispatch( 'darkmatterplugin/domains' ).removeDomain,
		};
	} ),
] )( Card );
