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
	jurorCount: {
		type: DataTypes.INTEGER,
		allowNull: false,
		defaultValue: 0,
	},
	active: {
		type: DataTypes.INTEGER,
		allowNull: false,
		defaultValue: 1,
	},
});
