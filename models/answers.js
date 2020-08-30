module.exports = (sequelize, DataTypes) => {
	const answers = sequelize.define('answers', {
		question_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		applicant_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		answer: {
			type: DataTypes.TEXT,
			allowNull: false,
			defaultValue: '',
		},
		active: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: true,
		},
	});

	answers.associate = models => {
		answers.belongsTo(models.applicants, {
			foreignKey: 'applicant_id',
			as: 'applicant',
		});
		answers.belongsTo(models.questions, {
			foreignKey: 'question_id',
			as: 'question',
		});

		answers.hasMany(models.scores, {
			foreignKey: {
				name: 'answer_id',
				allowNull: true,
			},
			as: 'scores',
		});
	};
	return answers;
};
