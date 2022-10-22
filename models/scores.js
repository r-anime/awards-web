module.exports = (sequelize, DataTypes) => {
	const scores = sequelize.define('scores', {
		answer_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		score: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		host_name: {
			type: DataTypes.STRING,
			allowNull: false,
		},
		note: {
			type: DataTypes.STRING,
			allowNull: true,
		},
		subgrade: {
			type: DataTypes.STRING,
			allowNull: false,
			defaultValue: '',
		},
	});

	scores.associate = models => {
		scores.belongsTo(models.answers, {
			foreignKey: 'answer_id',
			as: 'answer',
		});
	};
	return scores;
};
