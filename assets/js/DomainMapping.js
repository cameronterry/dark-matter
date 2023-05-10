/**
 * WordPress dependencies.
 */
import { createRoot, render } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import Grid from './components/domain/Grid';

import '../css/DomainMapping.css';

/**
 * Data Store
 */
import '../js/data/domains';

if ( document.body.classList.contains( 'settings_page_domains' ) ) {
	const rootElement = document.getElementById( 'root' );

	if ( createRoot ) {
		createRoot( rootElement ).render( <Grid /> );
	} else {
		render( <Grid />, rootElement );
	}
}
