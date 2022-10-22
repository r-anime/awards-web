'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('scores', 'subgrade', {
      type: Sequelize.DataTypes.STRING,
      allowNull: true,
      defaultValue: '',
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('scores', 'subgrades');
  }
};
