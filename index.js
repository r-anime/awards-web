const fs = require('fs');
const path = require('path');

const polka = require('polka'); // Web server
const sirv = require('sirv'); // Static file middleware
const session = require('express-session'); // Session storage middleware
const SQLiteStore = require('connect-sqlite3')(session);
const log = require('another-logger'); // Logging utility
const logging = require('./util/logging'); // Request logging middleware
const helpers = require('./util/helpers'); // Generic request/response helpers
const config = require('./config'); // Generic configuration

// Routes for non-frontend things
const api = require('./routes/api');
const auth = require('./routes/auth');

const indexPage = fs.readFileSync(path.join(config.publicDir, 'index.html'));

// Define the main application
polka({
	// The frontend uses history mode, so any route that isn't already defined
	// gets the frontend index page and Vue handles routing/rendering from there
	onNoMatch: (request, response) => response.end(indexPage),
})
	// Set up global middlewares
	.use(
		logging, // Request logging
		helpers, // Helper functions
		session({
			secret: config.session.secret,
			store: new SQLiteStore({
				db: 'animeawards.db',
				table: 'sessions',
			}),
		}),
		sirv(config.publicDir, {dev: true}), // Frontend assets
	)
	// Use the API routes and auth routes
	.use('/api', api)
	.use('/auth', auth)
	// Start the server
	.listen(config.port, () => {
		log.success(`Listening on port ${config.port}~!`);
	});
