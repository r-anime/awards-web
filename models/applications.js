module.exports = (sequelize, DataTypes) => {
	const applications = sequelize.define('applications', {
		start_note: {
			type: DataTypes.TEXT,
			allowNull: false,
			defaultValue: '',
		},
		end_note: {
			type: DataTypes.TEXT,
			allowNull: false,
			defaultValue: '',
		},
		year: {
			type: DataTypes.INTEGER,
			allowNull: false,
			defaultValue: 1917,
		},
		active: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});
	applications.associate = models => {
		applications.hasMany(models.applicants, {
			foreignKey: {
				name: 'app_id',
				allowNull: true,
			},
		});
		applications.hasMany(models.question_groups, {
			foreignKey: {
				name: 'app_id',
				allowNull: true,
			},
		});
	};
	return applications;
};
