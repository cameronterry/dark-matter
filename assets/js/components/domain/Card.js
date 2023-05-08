/**
 * WordPress Dependencies.
 *
 * @param props
 */
// import { __ } from '@wordpress/i18n';

/**
 * Card component.
 *
 * @param {Object} props Props.
 * @return {JSX.Element} Card JSX.
 */
export const Card = ( props ) => {
	const { domain } = props;

	return (
		<div className="dmp__domain-card">
			<h2>{ domain }</h2>

		</div>
	);
};
