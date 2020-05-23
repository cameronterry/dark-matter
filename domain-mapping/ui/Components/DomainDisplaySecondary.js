import React from 'react';

class DomainDisplaySecondary extends React.Component {
	/**
	 * Render component.
	 */
	render() {
		const url =
			( this.props.data.is_https ? 'https://' : 'http://' ) +
			this.props.data.domain;

		return (
			<td className="domain-options">
				<p>
					<a href={ url }>{ this.props.data.domain }</a>
				</p>
				{ this.props.data.is_active ? (
					<button onClick={ this.props.activate }>Deactivate</button>
				) : (
					<button onClick={ this.props.activate }>Activate</button>
				) }
				<span>|</span>
				<button onClick={ this.props.primary }>Set as Primary</button>
				<span>|</span>
				<button onClick={ this.props.protocol }>
					Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }
				</button>
				<span>|</span>
				<button className="submitdelete" onClick={ this.props.delete }>
					Delete
				</button>
			</td>
		);
	}
}

export default DomainDisplaySecondary;
