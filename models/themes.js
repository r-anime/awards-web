module.exports = (sequelize, types) => {
	const themes = sequelize.define('themes', {
		anime: {
			type: types.STRING,
			allowNull: false,
			defaultValue: '',
		},
		title: {
			type: types.STRING,
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

	themes.associate = models => {
		themes.hasMany(models.entries, {foreignKey: 'themeId', as: 'theme'});
	};

	return themes;
};
