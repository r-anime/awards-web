const {Client} = require('yuuko');
const config = require('../config'); // Generic configuration

const yuuko = new Client({
	token: config.discord.token || '', // Token used to auth your bot account
	prefix: '.', // Prefix used to trigger commands
});

module.exports = {
	yuuko,
};
