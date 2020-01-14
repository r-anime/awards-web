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
  return db.dropTable('public-votes');
};

exports.down = function(db) {
  return db.createTable('public-votes', {
    id: { type: 'int', primaryKey: true, autoIncrement: true},
    reddit_user: { type: 'string', notNull: true},
    user_id: { type: 'int'},
    category_id: { type: 'int', notNull: true },
    entry_id: { type: 'int', notNull: true},
    anilist_id: { type: 'int', notNull: false}, //Extraneous Field If You Need It, Leave Null If Not
  });
};

exports._meta = {
  "version": 1
};
