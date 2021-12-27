import { __, sprintf } from '@wordpress/i18n';
import React from 'react';

import DomainDisplayMedia from './DomainDisplayMedia';
import DomainDisplayPrimary from './DomainDisplayPrimary';
import DomainDisplaySecondary from './DomainDisplaySecondary';

class DomainRow extends React.Component {
	/**
	 * Handle the Activating / Deactivating of domains.
	 *
	 * @param {Object} event
	 */
	handleActivate = ( event ) => {
		event.preventDefault();

		const data = { ...this.props.domain };
		data.is_active = ! data.is_active;

		this.props.update( data );
	};

  /**
   * Handle converting domains between secondary to media domains and vice versa.
   *
   * @param {Object} event
   */
  handleConvert = ( event ) => {
	event.preventDefault();

	const data = { ...this.props.domain };

	if ( 1 === data.type ) {
		/** Convert media domain to secondary domain. */
		data.type = 2;
	} else if ( 2 === data.type ) {
		/** Convert media domain to secondary domain. */
		data.type = 1;
	}

	this.props.update( data );
  }

	/**
	 * Handle the Deleting of the domain.
	 *
	 * @param {Object} event
	 */
	handleDelete = ( event ) => {
		event.preventDefault();

		let message = '';

		if ( this.props.domain.is_primary ) {
			message = __(
				'Deleting the primary domain will stop any domain mapping for this Site. You will need to manually set another domain as primary. Do you wish to proceed?',
				'dark-matter'
			);
		} else {
			message = sprintf(
				/* translators: domain name */
				__( 'Are you sure you wish to delete %s?', 'dark-matter' ),
				this.props.domain.domain
			);
		}

		// eslint-disable-next-line no-alert
		const confirm = window.confirm( message );

		if ( ! confirm ) {
			return;
		}

		this.props.delete( this.props.domain.domain );
	};

	/**
	 * Handle the Setting the primary domain.
	 *
	 * @param {Object} event
	 */
	handlePrimary = ( event ) => {
		event.preventDefault();

		// eslint-disable-next-line no-alert
		const confirm = window.confirm(
			sprintf(
				/* translators: domain name */
				__(
					'Are you sure you wish to change %s to be the primary domain? This will cause 301 redirects and may affect SEO.',
					'dark-matter'
				),
				this.props.domain.domain
			)
		);

		if ( ! confirm ) {
			return;
		}

		const data = { ...this.props.domain };
		data.is_primary = true;

		this.props.update( data );
	};

	/**
	 * Handle the change in Protocol.
	 *
	 * @param {Object} event
	 */
	handleProtocol = ( event ) => {
		event.preventDefault();

		const data = { ...this.props.domain };
		const value = ! data.is_https;

		/**
		 * We only want to get confirmation from the user if we are changing to
		 * HTTPS.
		 */
		if ( value ) {
			// eslint-disable-next-line no-alert
			const confirm = window.confirm(
				sprintf(
					/* translators: domain name */
					__(
						'Please ensure that your server configuration includes %s for HTTPS. Do you wish to proceed?',
						'dark-matter'
					),
					this.props.domain.domain
				)
			);

			if ( ! confirm ) {
				return;
			}
		}

		data.is_https = value;

		this.props.update( data );
	};

	/**
	 * Render.
	 */
	render() {
		const { type } = this.props;

		if ( 1 === type ) {
			return this.renderMainDomain();
		} else if ( 2 === type ) {
			return this.renderMediaDomain();
		}
	}

	renderMainDomain() {
		return (
			<tr>
				{ this.props.domain.is_primary ? (
					<DomainDisplayPrimary
						data={ this.props.domain }
						activate={ this.handleActivate }
						protocol={ this.handleProtocol }
						delete={ this.handleDelete }
					/>
				) : (
					<DomainDisplaySecondary
						data={ this.props.domain }
						activate={ this.handleActivate }
						convert={ this.handleConvert }
						primary={ this.handlePrimary }
						protocol={ this.handleProtocol }
						delete={ this.handleDelete }
					/>
				) }
				<td>
					{ this.props.domain.is_primary
						? __( 'Yes', 'dark-matter' )
						: __( 'No', 'dark-matter' ) }
				</td>
				<td>
					{ this.props.domain.is_active
						? __( 'Yes', 'dark-matter' )
						: __( 'No', 'dark-matter' ) }
				</td>
				<td>
					{ this.props.domain.is_https
						? __( 'HTTPS', 'dark-matter' )
						: __( 'HTTP', 'dark-matter' ) }
				</td>
			</tr>
		);
	}

	renderMediaDomain() {
		return (
			<tr>
				<DomainDisplayMedia
					convert={ this.handleConvert }
					data={ this.props.domain }
					delete={ this.handleDelete }
				/>
				<td>
					This is a Media domain used for audio, images, video, etc.
				</td>
			</tr>
		);
	}
}

export default DomainRow;
