/* eslint-disable camelcase */

/**
 * WordPress Dependencies.
 */
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

		this.handlePrimary = this.handlePrimary.bind( this );
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
	 * @returns {JSX.Element}
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
			</div>
		);
	}
}

export default Card;
