'use strict';

var dbm;
var type;
var seed;

/**
  * We receive the dbmigrate dependency from dbmigrate initially.
  * This enables us to not have to rely on NODE_PATH.
  */
exports.setup = function(options, seedLink) {
  dbm = options.dbmigrate;
  type = dbm.dataType;
  seed = seedLink;
};

exports.up = function(db) {
  return db.createTable('themes',{
    id: {type: 'int', primaryKey: true, autoIncrement: true},
    anime: {type: 'string', notNull: true, defaultValue: ''},
    title: {type: 'string'},
    themeType: {type: 'string', notNull: true, defaultValue: ''},
    anilistID: {type: 'int', notNull: true},
    themeNo: {type: 'string'},
    link: {type: 'string', defaultValue: ''}
  });
};

exports.down = function(db) {
  return db.dropTable('themes');
};

exports._meta = {
  "version": 1
};