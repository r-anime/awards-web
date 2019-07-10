const bettersqlite3 = require('better-sqlite3');
const config = require('../config');

const db = bettersqlite3(config.db.location);

// Initial setup of the database
db.exec(`
	CREATE TABLE IF NOT EXISTS users (
		reddit
			TEXT
			PRIMARY KEY,
		flags
			INTEGER
			NOT NULL
			DEFAULT 0
	);
	CREATE TABLE IF NOT EXISTS categories (
		id
			INTEGER
			PRIMARY KEY
			AUTOINCREMENT,
		name
			TEXT
			NOT NULL
	);
	CREATE TABLE IF NOT EXISTS user_categories (
		id
			INTEGER
			PRIMARY KEY
			AUTOINCREMENT,
		user_reddit
			TEXT
			NOT NULL
			REFERENCES users(reddit),
		category_id
			INTEGER
			NOT NULL
			REFERENCES categories(id)
	);
`);

const userQuery = db.prepare('SELECT * FROM users WHERE reddit=?');
const allUsersQuery = db.prepare('SELECT * FROM users');
const insertUserQuery = db.prepare('INSERT INTO users (reddit, flags) VALUES (:reddit, :flags)');
const deleteUserQuery = db.prepare('DELETE FROM users WHERE reddit=?');

module.exports = {
	getUser: userQuery.get.bind(userQuery),
	getAllUsers: allUsersQuery.all.bind(allUsersQuery),
	insertUser: insertUserQuery.run.bind(insertUserQuery),
	deleteUser: deleteUserQuery.run.bind(deleteUserQuery),
};
