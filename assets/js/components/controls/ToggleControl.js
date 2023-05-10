import { useInstanceId } from '@wordpress/compose';

export const ToggleControl = ( {
	onChange,
	label,
	checked = false,
} ) => {
	const instanceId = useInstanceId( ToggleControl );
	const id = `dmp-toggle-${ instanceId }`;

	const onChangeToggle = ( event ) => {
		onChange( event.target.checked );
	};

	return (
		<div className="dmp__control-toggle">
			<label className="label" htmlFor={ id }>{ label }</label>
			<span className="control">
				<input id={ id } checked={ checked } onChange={ onChangeToggle } type="checkbox" /><label htmlFor={ id }>{ label }</label>
			</span>
		</div>
	);
};
