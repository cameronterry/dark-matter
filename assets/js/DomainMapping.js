/**
 * WordPress dependencies.
 */
import { createRoot, render } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import DomainManagement from './components/DomainManagement';

import '../css/DomainMapping.css';

/**
 * Data Store
 */
import '../js/data/domains';

if ( document.body.classList.contains( 'settings_page_domains' ) ) {
	const rootElement = document.getElementById( 'dmp-root' );

	if ( createRoot ) {
		createRoot( rootElement ).render( <DomainManagement /> );
	} else {
		render( <DomainManagement />, rootElement );
	}
}
