import React from 'react';
import { createRoot } from 'react-dom/client';
import DomainMapping from './Components/DomainMapping';

/**
 * We include React within the code for Dark Matter. WordPress does have React
 * but it's currently part of wp.element, but this is specifically for Gutenberg
 * and it's blocks. Which we do not need here.
 */

const container = document.getElementById( 'root' );
const root = createRoot( container );
root.render( <DomainMapping /> );
