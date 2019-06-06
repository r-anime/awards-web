import Dashboard from '../pages/host/Dashboard';
import GenreAllocation from '../pages/host/GenreAllocation';
import Users from '../pages/host/Users';

export default [
	{path: '', component: Dashboard},
	{path: 'allocation', component: GenreAllocation},
	{path: 'users', component: Users},
];
