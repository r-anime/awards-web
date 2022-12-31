'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('items', 'idMal', {
      type: Sequelize.DataTypes.STRING,
      allowNull: false,
      defaultValue: -1,
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('items', 'idMal');
  }
};
