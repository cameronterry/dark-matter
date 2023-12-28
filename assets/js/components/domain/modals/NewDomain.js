/**
 * To cut down on transformations and because the REST API is powered by PHP, we disable the camelcase for ES Lint.
 */
/* eslint-disable camelcase */

/**
 * WordPress dependencies
 */
import {
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
				<TextControl
					label={ __( 'Domain', 'darkmatterplugin' ) }
					onChange={ () => {
						setNewDomain( {} );
					} }
					value={ domain }
				/>
				<ToggleControl
					label={ __( 'Is Primary?', 'darkmatterplugin' ) }
					onChange={ () => {
						setNewDomain( { is_primary: ! is_primary } );
					} }
				/>
				<ToggleControl
					label={ __( 'Is HTTPS?', 'darkmatterplugin' ) }
					onChange={ () => {
						setNewDomain( { is_https: ! is_https } );
					} }
				/>
				<ToggleControl
					label={ __( 'Is Active?', 'darkmatterplugin' ) }
					onChange={ () => {
						setNewDomain( { is_active: ! is_active } );
					} }
				/>
			</Modal>
		</>
	);
};
