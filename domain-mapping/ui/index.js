import React from 'react';
import { render } from 'react-dom';
import DomainMapping from './Components/DomainMapping';

const DOMAINS = [
  { id: 1, domain : 'www.example.com', is_primary: true, active: true, is_https: false },
  { id: 2, domain : 'www.bbc.co.uk', is_primary: false, active: true, is_https: false }
];

render( <DomainMapping domains={DOMAINS} />, document.querySelector( '#root' ) );
