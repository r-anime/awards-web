module.exports = (sequelize, DataTypes) => {
	const feedback = sequelize.define('feedback', {
		feedback: {
			type: DataTypes.STRING,
			allowNull: false,
		},
		ip_hash: {
			type: DataTypes.STRING,
			allowNull: false,
		},
		reddit: {
			type: DataTypes.STRING,
			allowNull: true,
		},
		blacklist: {
			type: DataTypes.BOOLEAN,
			allowNull: false,
			defaultValue: false,
		},
	});

	return feedback;
};
