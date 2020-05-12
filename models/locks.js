module.exports = (sequelize, types) => sequelize.define('locks', {
	name: {
		type: types.STRING,
		allowNull: false,
	},
	flag: {
		type: types.BOOLEAN,
		allowNull: false,
	},
});
