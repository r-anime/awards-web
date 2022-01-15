'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('nominations', 'link', {
      type: Sequelize.DataTypes.STRING,
      allowNull: true,
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('nominations', 'link');
  }
};
