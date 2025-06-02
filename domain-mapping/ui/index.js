/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';

/**
 * Internal dependencies
 */
import DomainMapping from './Components/DomainMapping';

const container = document.getElementById( 'root' );
const root = createRoot( container );
root.render( <DomainMapping /> );
