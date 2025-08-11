/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

class DomainDisplayMedia extends Component {
	/**
	 * Render component
	 */
	render() {
		const url = 'https://' + this.props.data.domain;

		return (
			<td className="domain-options">
				<p>
					<em>
						<a href={ url }>{ this.props.data.domain }</a>
					</em>
				</p>
				<button onClick={ this.props.convert }>
					{ __( 'Convert to Secondary domain', 'dark-matter' ) }
				</button>
				<span>|</span>
				<button className="submitdelete" onClick={ this.props.delete }>
					{ __( 'Delete', 'dark-matter' ) }
				</button>
			</td>
		);
	}
}

export default DomainDisplayMedia;
