export const Card = ( props ) => {
	const { domain } = props;

	return (
		<div className="dmp__domain-card">
			<h2>{ domain }</h2>
		</div>
	);
};
