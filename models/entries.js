module.exports = (sequelize, types) => {
	const entries = sequelize.define('entries', {
		anilist_id: types.INTEGER,
		character_id: types.INTEGER,
	});

	entries.associate = models => {
		entries.belongsTo(models.themes, {foreignKey: 'themeId', as: 'Theme'});
	};

	entries.associate = models => {
		entries.belongsTo(models.categories, {foreignKey: 'categoryId', as: 'Category'});
		models.categories.hasMany(entries);
	};

	return entries;
};
