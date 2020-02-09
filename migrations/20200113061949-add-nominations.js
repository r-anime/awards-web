/* eslint-disable */

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
  return db.createTable('nominations', {
    id: { type: 'int', primaryKey: true, autoIncrement: true},
    category_id: {type: 'int', notNull: true},
    anilist_id: { type: 'int'},
    theme_id: {type: 'int'},
    entry_type: { type: 'string', notNull: true, defaultValue: 'shows' },
    active: { type: 'boolean', notNull: true, defaultValue: true},
  });
};

exports.down = function(db) {
  return db.dropTable('nominations');
};

exports._meta = {
  "version": 1
};
