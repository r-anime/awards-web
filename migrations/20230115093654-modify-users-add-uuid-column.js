'use strict';
module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('users', 'uuid', {
      type: Sequelize.DataTypes.STRING,
      allowNull: true,
		});
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('users', 'uuid');
  }
};
