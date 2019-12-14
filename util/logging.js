const log = require('another-logger');

module.exports = (request, response, next) => {
	log.hit(`${request.method} ${request.originalUrl}`);
	next();
};
