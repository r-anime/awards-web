/* eslint-disable no-unused-vars */


module.exports = {
	up: async (queryInterface, Sequelize) => queryInterface.sequelize.transaction(t => Promise.all([
		queryInterface.addColumn('categories', 'description', {
			type: Sequelize.DataTypes.STRING,
		}, {transaction: t}),
		queryInterface.addColumn('scores', 'note', {
			type: Sequelize.DataTypes.STRING,
		}, {transaction: t}),
	])),

	down: async (queryInterface, Sequelize) => queryInterface.sequelize.transaction(t => Promise.all([
		queryInterface.removeColumn('categories', 'description', {transaction: t}),
		queryInterface.removeColumn('scores', 'note', {transaction: t}),
	])),
};
