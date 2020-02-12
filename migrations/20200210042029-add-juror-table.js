/*eslint-disable*/
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
  return db.createTable('jurors', {
    id: { type: 'int', primaryKey: true, autoIncrement: true},
    category_id: {type: 'int', notNull: true},
    name: { type: 'string', defaultValue: '', length: 2048},
    link: {type: 'string', defaultValue: '', length: 2048},
    active: { type: 'boolean', notNull: true, defaultValue: true},
  });
};

exports.down = function(db) {
  return db.dropTable('jurors');
};

exports._meta = {
  "version": 1
};
