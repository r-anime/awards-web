module.exports = (sequelize, DataTypes) => {
	const qanswers = sequelize.define('question_answers', {
		question_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		answer: {
			type: DataTypes.STRING,
			allowNull: false,
			defaultValue: 'False',
		},
		order: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		active: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});
	qanswers.associate = models => {
		qanswers.belongsTo(models.questions, {
			foreignKey: 'question_id',
			as: 'question',
		});
	};
	return qanswers;
};
