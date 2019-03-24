const fs = require('fs');
const path = require('path');

const polka = require('polka'); // Web server
const sirv = require('sirv'); // Static file middleware
const session = require('express-session'); // Session storage middleware
const RDBStore = require('@geo1088/express-session-rethinkdb')(session); // Storage provider
const log = require('another-logger'); // Logging utility
const r = require('./util/db'); // Database connection
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
		session({ // Sessions and RethinkDB session storage
			secret: config.session.secret,
			store: new RDBStore({
				connection: r,
				table: config.session.table,
			}),
			// Next two options are required for RDBStore to work
			resave: true,
			saveUninitialized: true,
		}),
		sirv(config.publicDir), // Frontend assets
	)
	// Use the API routes and auth routes
	.use('/api', api)
	.use('/auth', auth)
	// Start the server
	.listen(config.port, () => {
		log.success(`Listening on port ${config.port}~!`);
	});
