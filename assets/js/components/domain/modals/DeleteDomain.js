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

export const DeleteDomain = ( {
	domain = null,
	isPrimary = false,
	onClose,
} ) => {
	const { removeDomain } = useDispatch( 'darkmatterplugin/domains' );
	const [ forceDelete, setForceDelete ] = useState( false );

	return (
		<>
			<Modal
				className="dmp__domain-modal"
				onRequestClose={ onClose }
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
						onClick={ () => {
							removeDomain( domain, forceDelete );
							onClose();
						} }
						variant="secondary"
					>
						{ __( 'Delete', 'darkmatterplugin' ) }
					</Button>
					<Button
						onClick={ onClose }
						variant="teritary"
					>
						{ __( 'Cancel', 'darkmatterplugin' ) }
					</Button>
				</div>
			</Modal>
		</>
	);
};
