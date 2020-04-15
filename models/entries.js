module.exports = (sequelize, types) => {
	const entries = sequelize.define('entries', {
		anilist_id: types.INTEGER,
		character_id: types.INTEGER,
	});

	entries.belongsTo(sequelize.import('./themes'));

	entries.belongsTo(sequelize.import('./categories'));

	sequelize.import('./categories').hasMany(entries);

	return entries;
};
