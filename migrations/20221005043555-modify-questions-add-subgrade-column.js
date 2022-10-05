'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('questions', 'subgrades', {
      type: Sequelize.DataTypes.STRING,
      allowNull: true,
      defaultValue: '',
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('questions', 'subgrades');
  }
};
