import React from 'react';
import { render } from 'react-dom';
import DomainMapping from './Components/DomainMapping';

import './App.scss';

/**
 * We include React within the code for Dark Matter. WordPress does have React
 * but it's currently part of wp.element, but this is specifically for Gutenberg
 * and it's blocks. Which we do not need here.
 */

render( <DomainMapping />, document.querySelector( '#root' ) );
