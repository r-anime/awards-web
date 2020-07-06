module.exports = (sequelize, DataTypes) => {
	const qgroups = sequelize.define('question_groups', {
		app_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		name: {
			type: DataTypes.STRING,
			allowNull: false,
		},
		order: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		weight: {
			type: DataTypes.FLOAT,
			allowNull: false,
			defaultValue: 1,
		},
		active: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});
	qgroups.associate = models => {
		qgroups.belongsTo(models.applications, {
			foreignKey: 'app_id',
			as: 'application',
		});

		qgroups.hasMany(models.questions, {
			foreignKey: {
				name: 'group_id',
				allowNull: true,
			},
		});
	};

	return qgroups;
};

