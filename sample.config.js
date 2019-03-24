const path = require('path');

module.exports = {
	// Server location
	host: 'localhost',
	port: process.env.PORT || 4567,
	// Directory where the compiled frontend goes
	publicDir: path.join(__dirname, 'public'),
	// RethinkDB connection information
	rethinkdb: {
		servers: [{
			host: 'localhost',
			port: 28015,
		}],
		db: 'animeawards_mkii',
	},
	// Session management options
	session: {
		secret: 'Some secret value', // Secret for cookies
		table: 'session', // RethinkDB table where session info is stored
	},
	// Reddit OAuth credentials
	reddit: {
		clientId: 'some client ID',
		clientSecret: 'some client secret',
	},
};
