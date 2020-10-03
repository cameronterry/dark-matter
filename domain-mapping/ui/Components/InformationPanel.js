import React from 'react';
import { __ } from '@wordpress/i18n';

class InformationPanel extends React.Component {
	/**
	 * Render component.
	 */
	render() {
		return (
			<aside className="info-panel sidebar">
				<div className="postbox">
					<h2>{ __( 'Dark Matter', 'dark-matter' ) }</h2>
					<p>{ __( 'Dark Matter is a highly opinionated domain mapping plugin for WordPress Networks, designed to work out of the box as-is with no setup. Unlike other plugins such as Donncha\'s "WordPress MU Domain Mapping" and WPMU Dev\'s premium domain mapping plugin, Dark Matter offers virtually no options beyond mapping individual domains.', 'dark-matter' ) }</p>
					<p><a href={ 'https://github.com/cameronterry/dark-matter' }>Visit Repository on Github</a></p>
				</div>
			</aside>
		);
	}
}

export default InformationPanel;
