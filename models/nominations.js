/* eslint-disable new-cap */
module.exports = (sequelize, types) => {
	const noms = sequelize.define('nominations', {
		anilist_id: types.INTEGER,
		character_id: types.INTEGER,
		writeup: {
			type: types.STRING(10000),
			defaultValue: '',
		},
		rank: types.INTEGER,
		votes: types.INTEGER,
		finished: {
			type: types.INTEGER,
			defaultValue: 0,
		},
		alt_name: {
			type: types.STRING(255),
			defaultValue: '',
		},
		staff: {
			type: types.STRING(1028),
			defaultValue: '',
		},
		alt_img: {
			type: types.STRING,
			defaultValue: '',
		},
		active: {
			type: types.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});

	noms.belongsTo(sequelize.import('./themes'));

	noms.belongsTo(sequelize.import('./categories'));

	sequelize.import('./categories').hasMany(noms);

	return noms;
};