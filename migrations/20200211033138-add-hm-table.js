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
  return db.createTable('honorable_mentions', {
    id: { type: 'int', primaryKey: true, autoIncrement: true},
    category_id: {type: 'int', notNull: true},
    name: { type: 'string', defaultValue: '', length: 2048},
    writeup: {type: 'string', defaultValue: '', length: 10000},
    active: { type: 'boolean', notNull: true, defaultValue: true},
  });
};

exports.down = function(db) {
  return db.dropTable('honorable_mentions');
};

exports._meta = {
  "version": 1
};
