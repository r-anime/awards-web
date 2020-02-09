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

exports.up = function(db) {
  return db.addColumn('nominations', 'writeup', {
    type: 'string',
    defaultValue: '',
    length: 10000
  }).then(function(){
    db.addColumn('nominations', 'rank', {
      type: 'int',
      length: 2
    });
  },function(){return ;}).then(function(){
    db.addColumn('nominations', 'votes', {
      type: 'int',
      length: 10
    });
  },function(){return ;});
};

exports.down = function(db, callback) {
  return null;
};

exports._meta = {
  "version": 1
};
