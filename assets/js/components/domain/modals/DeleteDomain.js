/**
 * WordPress dependencies
 */
import {
	Button,
	CheckboxControl,
	Modal,
} from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import {
	createInterpolateElement,
	useState,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

const FORCE_DELETE_DEFAULT = false;

/**
 * Modal for handling the domain deletion, with logic for Primary domains.
 *
 * @param {Object}  deleteDomain
 * @param {string}  deleteDomain.domain
 * @param {boolean} deleteDomain.isPrimary
 * @param {*}       deleteDomain.onClose
 * @return {JSX.Element} Modal for handling the deletion of the specified domain.
 * @class
 */
export const DeleteDomainModal = ( {
	domain = null,
	isPrimary = false,
	onClose,
} ) => {
	const { removeDomain } = useDispatch( 'darkmatterplugin/domains' );
	const [ forceDelete, setForceDelete ] = useState( FORCE_DELETE_DEFAULT );

	/**
	 * There is no point giving the option to save if the domain is a primary, and they have not checked the "force
	 * delete" option, as the REST API will just return an error.
	 */
	const deleteDisabled = ( isPrimary && ! forceDelete );

	const closeModal = () => {
		setForceDelete( FORCE_DELETE_DEFAULT );

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
				title={
					sprintf(
						/* translators: %s: domain. */
						__( 'Delete: %s', 'darkmatterplugin' ),
						domain
					)
				}
			>
				<p>
					{
						createInterpolateElement(
							sprintf(
								/* translators: %s: domain. */
								__( 'Are you sure you wish to delete, <strong>%1$s</strong>? This action cannot be undone.', 'darkmatterplugin' ),
								domain
							),
							{
								strong: <strong />,
							}
						)
					}
				</p>
				{
					isPrimary && (
						<>
							<p>
								{
									createInterpolateElement(
										sprintf(
											/* translators: %s: domain. */
											__( '<strong>%1$s</strong>, is a primary domain, and deleting this domain can negatively impact visitors to your website and SEO.', 'darkmatterplugin' ),
											domain
										),
										{
											strong: <strong />,
										}
									)
								}
							</p>
							<CheckboxControl
								label={ __( 'Force delete?', 'darkmatterplugin' ) }
								onChange={ () => {
									setForceDelete( ! forceDelete );
								} }
								checked={ forceDelete }
							/>
						</>
					)
				}
				<div className="dmp__domain-modal-buttons">
					<Button
						className="dmp__delete is-destructive"
						disabled={ deleteDisabled }
						onClick={ () => {
							removeDomain( domain, forceDelete );
							closeModal();
						} }
						variant="secondary"
					>
						{ __( 'Delete', 'darkmatterplugin' ) }
					</Button>
					<Button
						onClick={ closeModal }
						variant="teritary"
					>
						{ __( 'Cancel', 'darkmatterplugin' ) }
					</Button>
				</div>
			</Modal>
		</>
	);
};
