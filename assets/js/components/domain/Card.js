/* eslint-disable camelcase */

/**
 * WordPress Dependencies.
 *
 * @param props
 */
import { __ } from '@wordpress/i18n';

import { ToggleControl } from '../controls/ToggleControl';

/**
 * Card component.
 *
 * @param {Object} props Props.
 * @return {JSX.Element} Card JSX.
 */
export const Card = ( props ) => {
	const { domain, is_active, is_https, is_primary, type } = props;

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
			<ToggleControl checked={ is_primary } label={ __( 'Primary', 'darkmatterplugin' ) } onChange={ ( e ) => {
				console.log( e ); // eslint-disable-line
			} } />
			<ToggleControl checked={ is_active } label={ __( 'Active', 'darkmatterplugin' ) } onChange={ ( e ) => {
				console.log( e ); // eslint-disable-line
			} } />
		</div>
	);
};
