const config = require('../config');
const rethinkdbdash = require('rethinkdbdash');

const rdbconfig = Object.assign({silent: true}, config.rethinkdb);

module.exports = rethinkdbdash(rdbconfig);
