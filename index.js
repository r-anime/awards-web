const fs = require('fs');
const http = require('http');
const https = require('https');
const path = require('path');

const polka = require('polka'); // Web server
const sirv = require('sirv'); // Static file middleware
const session = require('express-session'); // Session storage middleware
const SequelizeStore = require('connect-session-sequelize')(session.Store);
const log = require('another-logger'); // Logging utility
const logging = require('./util/logging'); // Request logging middleware
const helpers = require('./util/helpers'); // Generic request/response helpers
const config = require('./config'); // Generic configuration

const models = require('./models');

// Routes for non-frontend things
const api = require('./routes/api');
const auth = require('./routes/auth');

const indexPage = fs.readFileSync(path.join(config.publicDir, 'index.html'));
const hostPage = fs.readFileSync(path.join(config.publicDir, 'host.html'));

// Define the main application
const app = polka({
	// The frontend uses history mode, so any route that isn't already defined
	// gets the frontend index page and Vue handles it from there (including
	// rendering a "not found" page when appropriate)
	onNoMatch: (request, response) => response.end(indexPage),
});

// Set up global middlewares
app.use(
	// Request logging
	logging,
	// Helper functions
	helpers,
	// Session storage
	session({
		secret: config.session.secret,
		resave: false,
		saveUninitialized: false,
		store: new SequelizeStore({
			db: models.sequelize,
		}),
	}),
	// Static assets
	sirv(config.publicDir, {
		dev: true, // HACK: dev mode to skip caching potentially incomplete webpack bundles, since for some reason they get regenerated at random times and it breaks the site
		gzip: true,
	}),
);

// Register the API routes and auth routes
app.use('/api', api);
app.use('/auth', auth);

// Register API routes for webpack entrypoints
app.use('/host', (request, response) => response.end(hostPage));
// Login is a stupid route that needs to be handled better and hosted at /host instead of /login
app.use('/login', (request, response) => response.end(hostPage));

// Synchronize sequelize models
// and then start the server
models.sequelize.sync().then(() => {
	// Register Heather and Geo as admins so that we don't have to manually insert rows and fuck with sequelize
	models.sequelize.model('users').findOrCreate({
		where: {
			reddit: 'EpicTroll27',
		},
		defaults: {
			level: 4,
		},
	});
	models.sequelize.model('users').findOrCreate({
		where: {
			reddit: 'geo1088',
		},
		defaults: {
			level: 4,
		},
	});
	if (config.https) {
		// If we're using HTTPS, create an HTTPS server
		const httpsOptions = {
			key: config.https.key,
			cert: config.https.cert,
		};
		const httpsApp = https.createServer(httpsOptions, app.handler);
		httpsApp.listen(config.https.port, () => {
			log.success(`HTTPS listening on port ${config.https.port}`);
		});
		// The HTTP server will just redirect to the HTTPS server
		http.createServer((req, res) => {
			res.writeHead(301, {Location: `https://${req.headers.host}${req.url}`});
			res.end();
		}).listen(config.port, () => {
			log.success(`HTTP redirect listening on port ${config.port}`);
		});
	} else {
		app.listen(config.port, () => {
			log.success(`Listening on port ${config.port}~!`);
		});
	}
});
