module.exports = (sequelize, types) => {
	const entries = sequelize.define('entries', {
		anilist_id: types.INTEGER,
		character_id: types.INTEGER,
		search: types.STRING,
		searchSchema: {
			type: types.VIRTUAL,
			get () {
				const searchArr = this.search.split('%').filter(el => el !== '');

				if (searchArr.length === 2) {
					return {
						character: searchArr[0],
						anime: searchArr[1],
					};
				} else if (searchArr.length === 3) {
					return {
						character: searchArr[0],
						va: searchArr[1],
						anime: searchArr[2],
					};
				} else if (searchArr.length === 1) {
					return {
						character: searchArr[0],
					};
				} else if (searchArr.length > 3) {
					return {
						anime: searchArr[searchArr.length - 1],
						character: searchArr[0],
						synonyms: searchArr.slice(1, searchArr.length - 1),
					};
				}
				return {};
			},
			set () {
				throw new Error('No');
			},
		},	
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
