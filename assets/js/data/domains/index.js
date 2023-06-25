import { createReduxStore, register } from '@wordpress/data';

import * as actions from './actions';
import * as controls from './controls';
import * as resolvers from './resolvers';
import * as selectors from './selectors';

const DEFAULT_STATE = {
	domains: [],
	pagination: {
		current: 0,
		totalItems: 0,
		totalPages: 0,
	},
};

const store = createReduxStore( 'darkmatterplugin/domains', {
	reducer( state = DEFAULT_STATE, action = {} ) {
		switch ( action.type ) {
			case 'REMOVE_DOMAIN': {
				const removeIndex = state.domains.findIndex( ( item ) => {
					return item.domain === action.domain;
				} );

				state.domains.splice( removeIndex, 1 );

				return {
					...state,
					pagination: {
						...state.pagination,
						totalItems: state.pagination.totalItems - 1,
					},
				};
			}
			case 'SET_DOMAINS': {
				return {
					...state,
					domains: [ ...action.domains ],
					pagination: {
						...state.pagination,
						...action.pagination,
					},
				};
			}
			case 'UPDATE_DOMAIN': {
				const updateIndex = state.domains.findIndex( ( item ) => {
					return item.domain === action.domain;
				} );

				return {
					...state,
					domains: [
						...state.slice( 0, updateIndex ),
						{
							...action,
						},
						...state.slice( updateIndex + 1 ),
					],
				};
			}
			default:
				return state;
		}
	},
	actions,
	controls,
	resolvers,
	selectors,
} );

register( store );
