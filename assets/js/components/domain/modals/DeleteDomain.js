/**
 * WordPress dependencies
 */
import {
	Button,
	CheckboxControl,
	Modal,
} from '@wordpress/components';
import {
	createInterpolateElement,
	useState,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

export const DeleteDomain = ( {
	domain = null,
	isPrimary = false,
	onClose = null,
} ) => {
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
				{
					isPrimary ? (
						<>
							<p>
								{
									createInterpolateElement(
										sprintf(
											/* translators: %s: domain. */
											__( 'Are you sure you wish to delete, <strong>%1$s</strong>?', 'darkmatterplugin' ),
											domain
										),
										{
											strong: <strong />,
										}
									)
								}
							</p>
							<p>
								{
									createInterpolateElement(
										sprintf(
											/* translators: %s: domain. */
											__( '<strong>%1$s</strong>, is a primary domain, and removing can negatively impact visitors to your website and SEO.', 'darkmatterplugin' ),
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
								value={ forceDelete }
							/>
						</>
					) : (
						<p>
							{
								sprintf(
									/* translators: %s: domain. */
									__( 'Are you sure you wish to delete, %s?', 'darkmatterplugin' ),
									domain
								)
							}
						</p>
					)
				}
				<div className="dmp__domain-modal-buttons">
					<Button
						variant="primary"
						className="dmp__delete is-destructive"
					>
						{ __( 'Delete', 'darkmatterplugin' ) }
					</Button>
					<Button
						variant="secondary"
						onClick={ onClose }
					>
						{ __( 'Cancel', 'darkmatterplugin' ) }
					</Button>
				</div>
			</Modal>
		</>
	);
};
