'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('nominations', 'watched', {
      type: Sequelize.DataTypes.INTEGER,
      allowNull: true,
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('nominations', 'watched');
  }
};
