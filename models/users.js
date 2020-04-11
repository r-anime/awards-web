module.exports = (sequelize, types) => sequelize.define('users', {
	reddit: {
		type: types.STRING,
		allowNull: false,
		defaultValue: 0,
	},
	level: {
		type: types.INTEGER,
		allowNull: false,
		defaultValue: 0,
	},
	flags: {
		type: types.INTEGER,
		allowNull: false,
		defaultValue: 0,
	},
});
