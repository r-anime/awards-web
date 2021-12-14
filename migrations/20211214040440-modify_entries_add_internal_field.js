'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
		await queryInterface.addColumn('entries', 'internal', {
      type: Sequelize.DataTypes.BOOLEAN,
      allowNull: false,
      defaultValue: false,
		});
  },

	down: async (queryInterface, Sequelize) => {
		await queryInterface.removeColumn('entries', 'internal');
  },
};
