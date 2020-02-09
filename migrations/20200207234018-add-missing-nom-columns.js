/*eslint-disable*/
'use strict';

var dbm;
var type;
var seed;
var async = require('async');

/**
  * We receive the dbmigrate dependency from dbmigrate initially.
  * This enables us to not have to rely on NODE_PATH.
  */
exports.setup = function(options, seedLink) {
  dbm = options.dbmigrate;
  type = dbm.dataType;
  seed = seedLink;
};

exports.up = function(db, callback) {
  async.series([
    db.addColumn.bind(db, 'nominations', 'finished', {
      type: 'int',
      defaultValue: 0
    }),
    db.addColumn.bind(db, 'nominations', 'alt_name', {
      type: 'string',
      defaultValue: '',
      length: 255
    }),
    db.addColumn.bind(db, 'nominations', 'staff', {
      type: 'string',
      defaultValue: '',
      length: 1028
    })
  ], callback);
};

exports.down = function(db, callback) {
  return null;
};

exports._meta = {
  "version": 1
};
