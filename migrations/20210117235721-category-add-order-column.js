'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => queryInterface.sequelize.transaction(t => Promise.all([
		queryInterface.addColumn('categories', 'order', {
      type: Sequelize.DataTypes.INTEGER,
      defaultValue: 1,
		}, {transaction: t})
	])),

	down: async (queryInterface, Sequelize) => queryInterface.sequelize.transaction(t => Promise.all([
		queryInterface.removeColumn('categories', 'order', {transaction: t})
	])),
};
