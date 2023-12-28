/**
 * WordPress dependencies
 */
import {
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const NewDomainModal = ( {
	onClose,
} ) => {
	const closeModal = () => {
		/**
		 * Handle the supplied onClose event handler.
		 */
		if ( onClose ) {
			onClose();
		}
	};

	return (
		<>
			<Modal
				className="dmp__domain-modal"
				onRequestClose={ closeModal }
				size="medium"
				title={ __( 'Add New Domain', 'darkmatterplugin' ) }
			>
				<p>Hello world</p>
			</Modal>
		</>
	);
};
