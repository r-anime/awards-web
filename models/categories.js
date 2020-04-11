module.exports = (sequelize, DataTypes) => sequelize.define('categories', {
	name: {
		type: DataTypes.STRING,
		allowNull: false,
	},
	entryType: {
		type: DataTypes.STRING,
		allowNull: false,
		defaultValue: 'shows',
	},
	awardsGroup: {
		type: DataTypes.STRING,
		allowNull: false,
		defaultValue: 'genre',
	},
	active: {
		type: DataTypes.INTEGER,
		allowNull: false,
		defaultValue: 1,
	},
});
