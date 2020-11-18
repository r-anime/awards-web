module.exports = {
	up: (queryInterface, Sequelize) => queryInterface.sequelize.transaction(t => queryInterface.addColumn('entries', 'search', {
		type: Sequelize.DataTypes.STRING,
	}, {transaction: t})),

	down: queryInterface => queryInterface.sequelize.transaction(t => queryInterface.removeColumn('entries', 'search', {transaction: t})),
};
