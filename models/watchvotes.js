'use strict';
const {
  Model
} = require('sequelize');
module.exports = (sequelize, DataTypes) => {
  class WatchVotes extends Model {
    
  };
  WatchVotes.init({
    reddit_user: DataTypes.STRING,
    anilist_id: DataTypes.INTEGER,
    name: DataTypes.STRING,
    status: DataTypes.INTEGER
  }, {
    sequelize,
    modelName: 'WatchVotes',
  });
  return WatchVotes;
};