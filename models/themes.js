module.exports = (sequelize, types) => sequelize.define('nominations', {
	anime: {
		type: types.STRING,
		allowNull: false,
		defaultValue: '',
	},
	title: {
		type: types.INTEGER,
	},
	themeType: {
		type: types.STRING,
		defaultValue: '',
		allowNull: false,
	},
	anilistID: {
		type: types.INTEGER,
		allowNull: false,
	},
	themeNo: types.INTEGER,
	link: {
		type: types.STRING,
		defaultValue: '',
	},
});
