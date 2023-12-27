/**
 * WordPress dependencies
 */
import { Button, ToggleControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
// import { ToggleControl } from '../controls/ToggleControl';

const DOMAIN_TYPE = {
	1: __( 'Main', 'darkmatterplugin' ),
	2: __( 'Media', 'darkmatterplugin' ),
};

class Table extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			fields: props.fields ?? [
				{
					name: 'domain',
					label: __( 'Domain', 'darkmatterplugin' ),
					visible: true,
				},
				{
					name: 'type',
					label: __( 'Type', 'darkmatterplugin' ),
					visible: true,
				},
				{
					name: 'is_primary',
					label: __( 'Primary?', 'darkmatterplugin' ),
					type: 'toggle',
					visible: true,
				},
				{
					name: 'is_active',
					label: __( 'Active?', 'darkmatterplugin' ),
					type: 'toggle',
					visible: true,
				},
				{
					name: 'actions',
					label: __( 'Actions', 'darkmatterplugin' ),
					visible: true,
				},
			],
		};
	}

	render() {
		return (
			<div className="dmp__domain-table-wrapper">
				<table className="dmp__domain-table">
					{ this.renderHeaders() }
					{ this.renderDomains() }
				</table>
			</div>
		);
	}

	renderDisplay( field, domain ) {
		if ( 'actions' === field.name ) {
			return (
				<>
					<Button
						icon="trash"
						label={ __( 'Remove domain', 'darkmatterplugin' ) }
					/>
				</>
			);
		} else if ( 'type' === field.name ) {
			const domainType = domain[ field.name ];
			if ( DOMAIN_TYPE[ domainType ] ) {
				return DOMAIN_TYPE[ domainType ];
			}

			return __( 'Custom Type', 'darkmatterplugin' );
		} else if ( 'toggle' === field.type ) {
			return <ToggleControl
				checked={ domain[ field.name ] }
				label={ field.label }
				onChange={ ( e ) => {
					console.log( e ); // eslint-disable-line
				} }
			/>;
		}

		return domain[ field.name ];
	}

	renderDomains() {
		const { getDomains } = this.props;

		const domains = getDomains();

		const { fields } = this.state;
		const displayFields = fields.filter( ( field ) => field.visible );

		return (
			<tbody>
				{ domains.length > 0 && domains.map( ( domain ) => {
					return (
						<tr key={ `domain-row-${ domain.id }` }>
							{ displayFields.length > 0 && displayFields.map( ( field ) => {
								const className = classNames( 'dmp__domain-column', `dmp__domain-field-${ field.name }` );

								return (
									<td
										key={ `domain-cell-${ field.name }-${ domain.id }` }
										className={ className }
									>
										{ this.renderDisplay( field, domain ) }
									</td>
								);
							} ) }
						</tr>
					);
				} ) }
			</tbody>
		);
	}

	renderHeaders() {
		// Domain, Type, Active, Edit/Remove
		const { fields } = this.state;
		const displayFields = fields.filter( ( field ) => field.visible );

		return (
			<thead>
				<tr>
					{ displayFields.length > 0 && displayFields.map( ( field ) => (
						<th key={ `domain-header-${ field.name }` }>
							{ field.label }
						</th>
					) ) }
				</tr>
			</thead>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		return {
			getDomains: select( 'darkmatterplugin/domains' ).getDomains,
			pagination: select( 'darkmatterplugin/domains' ).getPagination(),
		};
	} ),
] )( Table );
