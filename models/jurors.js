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
	});

	jurors.associate = models => {
		jurors.belongsTo(models.categories, {foreignKey: 'categoryId', as: 'category'});
		models.categories.hasMany(jurors);
	};

	jurors.associate = models => {
		jurors.belongsTo(models.users, {foreignKey: 'name', as: 'reddit'});
		models.users.hasMany(jurors);
	};

	return jurors;
};
