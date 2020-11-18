module.exports = (sequelize, types) => {
	const entries = sequelize.define('entries', {
		anilist_id: types.INTEGER,
		character_id: types.INTEGER,
		search: types.STRING,
	});

	entries.associate = models => {
		entries.belongsTo(models.themes, {foreignKey: 'themeId', as: 'theme'});
	};

	entries.associate = models => {
		entries.belongsTo(models.categories, {foreignKey: 'categoryId', as: 'category'});
		models.categories.hasMany(entries);
	};

	return entries;
};
