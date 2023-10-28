'use strict';
const { Model } = require('sequelize');
module.exports = (sequelize, DataTypes) => {
  class acknowledgements extends Model {
    /**
     * Helper method for defining associations.
     * This method is not a part of Sequelize lifecycle.
     * The `models/index` file will call this method automatically.
     */
    static associate(models) {
      // define association here
    }
  }
  acknowledgements.init({
    year: {
			type: DataTypes.INTEGER,
			allowNull: false,
		},
    name: {
			type: DataTypes.STRING,
			allowNull: false,
		},
    originalText: {
			type: DataTypes.STRING,
			allowNull: false,
		},
    translatedText: {
			type: DataTypes.STRING,
			allowNull: true,
		}
  }, {
    sequelize,
    modelName: 'acknowledgements',
  });
  return acknowledgements;
};