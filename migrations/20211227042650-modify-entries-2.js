'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
		await queryInterface.addColumn('items', 'internal', {
      type: Sequelize.DataTypes.BOOLEAN,
      allowNull: false,
      defaultValue: false,
		});
    await queryInterface.addColumn('items', 'mediatype', {
      type: Sequelize.DataTypes.STRING,
		});
    await queryInterface.addColumn('items', 'names', {
      type: Sequelize.DataTypes.STRING,
		});
    await queryInterface.removeColumn('entries', 'internal');
  },

	down: async (queryInterface, Sequelize) => {
		await queryInterface.removeColumn('items', 'internal');
    await queryInterface.removeColumn('items', 'mediatype');
    await queryInterface.removeColumn('items', 'names');
    await queryInterface.addColumn('entries', 'internal', {
      type: Sequelize.DataTypes.BOOLEAN,
      allowNull: false,
      defaultValue: false,
		});
  },
};
