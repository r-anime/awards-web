/* eslint-disable new-cap */
module.exports = (sequelize, types) => {
	const jurors = sequelize.define('jurors', {
		name: {
			type: types.STRING(2048),
			defaultValue: '',
		},
		link: {
			type: types.STRING(2048),
			defaultValue: '',
		},
		score: {
			type: types.FLOAT,
			defaultValue: 0,
		},
		preference: {
			type: types.INTEGER,
			defaultValue: 0,
		},
		active: {
			type: types.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
		categoryId: {
			type: types.INTEGER,
			defaultValue: -1,
			allowNull: false,
		}
	});

	jurors.associate = models => {
		jurors.belongsTo(models.categories, {foreignKey: 'categoryId', as: 'category'});
		// models.categories.hasMany(jurors);

		jurors.belongsTo(models.users, {foreignKey: 'name', targetKey: 'reddit', as: 'user'});
		// models.users.hasMany(jurors);
	};

	return jurors;
};
