const log = require('another-logger');
const superagent = require('superagent');
const app = require('polka')();

app.post('/send', async (request, response) => {
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}

	let userAgent = request.headers['user-agent'];
	var ip = request.headers['x-forwarded-for'] || request.socket.remoteAddress;
	if (ip.substr(0, 7) == '::ffff:') {
		ip = ip.substr(7);
	}

	let headers = {
		'user-agent': userAgent,
		'x-forwarded-for': ip,
	};

	// console.log(req);

	const anal = await superagent.post('https://plausible.io/api/event')
						.type('text/plain')
						.set('user-agent', userAgent)
						.set('x-forwarded-for', ip)
						.send(JSON.stringify(req))
						.then(() => {
							response.json({success: true});
						})
						.catch(e => {
							console.log(e);
							return response.error(e);
						});
	
});

module.exports = app;
