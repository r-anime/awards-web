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
	});

	scores.associate = models => {
		scores.belongsTo(models.answers, {
			foreignKey: 'answer_id',
			as: 'answer',
		});
	};
	return scores;
};
