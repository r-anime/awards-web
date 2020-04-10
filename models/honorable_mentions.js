/* eslint-disable new-cap */
module.exports = (sequelize, DataTypes) => sequelize.define('honorable_mentions', {
	category_id: {
		type: DataTypes.INTEGER,
		allowNull: false,
	},
	name: {
		type: DataTypes.STRING(2048),
		defaultValue: '',
	},
	writeup: {
		type: DataTypes.STRING(10000),
		defaultValue: '',
	},
	active: {
		type: DataTypes.BOOLEAN,
		allowNull: false,
		defaultValue: true,
	},
});
