module.exports = (sequelize, DataTypes) => {
	const applicants = sequelize.define('applicants', {
		app_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		user_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		active: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});

	applicants.associate = models => {
		applicants.belongsTo(models.applications, {
			foreignKey: 'app_id',
			as: 'application',
		});

		applicants.hasMany(models.answers, {
			foreignKey: {
				name: 'applicant_id',
				allowNull: true,
			},
		});
	};

	return applicants;
};
