module.exports = (sequelize, DataTypes) => {
	const questions = sequelize.define('questions', {
		group_id: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
		question: {
			type: DataTypes.TEXT,
			allowNull: false,
		},
		type: {
			type: DataTypes.STRING, // ENUM: essay, simple, multiple_choice
			allowNull: false,
			defaultValue: 'essay',
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

	questions.associate = models => {
		questions.belongsTo(models.question_groups, {
			foreignKey: 'group_id',
			as: 'question_group',
		});

		questions.hasMany(models.answers, {
			foreignKey: {
				name: 'question_id',
				allowNull: true,
			},
		});

		questions.hasMany(models.question_answers, {
			foreignKey: {
				name: 'question_id',
				allowNull: true,
			},
		});
	};
	return questions;
};
