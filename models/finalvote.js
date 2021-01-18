'use strict';
const {
  Model
} = require('sequelize');
module.exports = (sequelize, DataTypes) => {
  class finalvote extends Model {
    /**
     * Helper method for defining associations.
     * This method is not a part of Sequelize lifecycle.
     * The `models/index` file will call this method automatically.
     */
    static associate(models) {
      // define association here
    }
  };
  finalvote.init({
    id: DataTypes.INTEGER,
    reddit_user: DataTypes.STRING,
    category_id: DataTypes.INTEGER,
    nom_id: DataTypes.INTEGER,
    anilist_id: DataTypes.INTEGER,
    theme_name: DataTypes.STRING
  }, {
    sequelize,
    modelName: 'finalvote',
  });
  return finalvote;
};