const fs = require('fs');
const path = require('path');
const {Sequelize, DataTypes, Model} = require('sequelize');
const basename = path.basename(__filename);
const db = {};

const config = require('../config');

const sequelize = new Sequelize({
	dialect: 'sqlite',
	storage: path.join(config.db.path, config.db.filename),
	logging: false,
	queries: {
		raw: true,
	},
	retry: {
		match: [
			/SQLITE_BUSY/,
		],
		max: 10,
	},
	pool: {
		max: 10,
	},
	transactionType: 'IMMEDIATE',
});


fs
	.readdirSync(__dirname)
	.filter(file => file.indexOf('.') !== 0 && file !== basename && file.slice(-3) === '.js')
	.forEach(file => {
		// eslint-disable-next-line global-require
		const model = require(path.join(__dirname, file))(sequelize, DataTypes, Model);
		db[model.name] = model;
	});

Object.keys(db).forEach(modelName => {
	if (db[modelName].associate) {
		db[modelName].associate(db);
	}
});

db.sequelize = sequelize;
db.Sequelize = Sequelize;

module.exports = db;
