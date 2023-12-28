/**
 * To cut down on transformations and because the REST API is powered by PHP, we disable the camelcase for ES Lint.
 */
/* eslint-disable camelcase */

/**
 * WordPress dependencies
 */
import {
	Button,
	Modal,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const NEW_DOMAIN_DEFAULT = {
	domain: '',
	is_primary: false,
	is_https: true,
	is_active: true,
	type: 'main',
};

export const NewDomainModal = ( {
	onClose,
} ) => {
	const [ newDomain, setNewDomain ] = useState( NEW_DOMAIN_DEFAULT );

	const closeModal = () => {
		setNewDomain( NEW_DOMAIN_DEFAULT );

		/**
		 * Handle the supplied onClose event handler.
		 */
		if ( onClose ) {
			onClose();
		}
	};

	const {
		domain,
		is_primary,
		is_https,
		is_active,
	} = newDomain;

	return (
		<>
			<Modal
				className="dmp__domain-modal"
				onRequestClose={ closeModal }
				size="medium"
				title={ __( 'Add New Domain', 'darkmatterplugin' ) }
			>
				<form>
					<TextControl
						label={ __( 'Domain', 'darkmatterplugin' ) }
						onChange={ ( value ) => {
							setNewDomain( { ...newDomain, domain: value } );
						} }
						value={ domain }
					/>
					<ToggleControl
						checked={ is_primary }
						help={
							is_primary
								? __( 'Your website will use this domain. All other main domains will redirect to this domain.', 'darkmatterplugin' )
								: __( 'A secondary domain. Requests to this domain will be redirected to the primary domain.', 'darkmatterplugin' )
						}
						label={ __( 'Is Primary?', 'darkmatterplugin' ) }
						onChange={ () => {
							setNewDomain( { ...newDomain, is_primary: ! is_primary } );
						} }
					/>
					<ToggleControl
						checked={ is_https }
						help={
							is_https
								? __( 'Use HTTPS', 'darkmatterplugin' )
								: __( 'Use HTTP. Note: not recommended as it will decrease the security and SEO of your website.', 'darkmatterplugin' )
						}
						label={ __( 'Is HTTPS?', 'darkmatterplugin' ) }
						onChange={ () => {
							setNewDomain( { ...newDomain, is_https: ! is_https } );
						} }
					/>
					<ToggleControl
						checked={ is_active }
						help={
							is_active
								? ''
								: __( 'Redirects will not engaged. Useful for testing purposes.', 'darkmatterplugin' )
						}
						label={ __( 'Is Active?', 'darkmatterplugin' ) }
						onChange={ () => {
							setNewDomain( { ...newDomain, is_active: ! is_active } );
						} }
					/>
					<div className="dmp__domain-modal-buttons">
						<Button
							onClick={ closeModal }
							variant="teritary"
						>
							{ __( 'Cancel', 'darkmatterplugin' ) }
						</Button>
						<Button
							variant="primary"
						>
							{ __( 'Add Domain', 'darkmatterplugin' ) }
						</Button>
					</div>
				</form>
			</Modal>
		</>
	);
};
