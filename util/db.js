const path = require('path');
const bettersqlite3 = require('better-sqlite3');
const config = require('../config');

const db = bettersqlite3(path.join(config.db.path, config.db.filename));

// Initial setup of the database
db.exec(`
	CREATE TABLE IF NOT EXISTS users (
		reddit
			TEXT
			PRIMARY KEY,
		level
			INTEGER
			NOT NULL
			DEFAULT 0,
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
			NOT NULL,
		entry_type
			TEXT
			NOT NULL
			CHECK(entry_type in ('shows', 'characters', 'vas', 'themes'))
			DEFAULT 'shows'
	);
	CREATE TABLE IF NOT EXISTS category_entries (
		category_id
			INTEGER
			REFERENCES categories(id),
		entry_anilist_id
			INTEGER
			NOT NULL,
		PRIMARY KEY (category_id, entry_anilist_id)
	);
`);

// Define all our queries
const getUserQuery = db.prepare('SELECT * FROM users WHERE reddit=?');
const getAllUsersQuery = db.prepare('SELECT * FROM users');
const insertUserQuery = db.prepare('INSERT INTO users (reddit, level, flags) VALUES (:reddit, :level, :flags)');
const deleteUserQuery = db.prepare('DELETE FROM users WHERE reddit=?');

const getCategoryQuery = db.prepare('SELECT * FROM categories WHERE id=?');
const getCategoryByRowidQuery = db.prepare('SELECT * FROM categories WHERE rowid=?');
const getAllCategoriesQuery = db.prepare('SELECT * FROM categories');
const insertCategoryQuery = db.prepare('INSERT INTO categories (name) VALUES (:name)');
const updateCategoryQuery = db.prepare('UPDATE categories SET name=:name WHERE id=:id');
const deleteCategoryQuery = db.prepare('DELETE FROM categories WHERE id=?');

module.exports = {
	getUser: getUserQuery.get.bind(getUserQuery),
	getAllUsers: getAllUsersQuery.all.bind(getAllUsersQuery),
	insertUser: insertUserQuery.run.bind(insertUserQuery),
	deleteUser: deleteUserQuery.run.bind(deleteUserQuery),

	getCategory: getCategoryQuery.get.bind(getCategoryQuery),
	getCategoryByRowid: getCategoryByRowidQuery.get.bind(getCategoryByRowidQuery),
	getAllCategories: getAllCategoriesQuery.all.bind(getAllCategoriesQuery),
	insertCategory: insertCategoryQuery.run.bind(insertCategoryQuery),
	updateCategory: updateCategoryQuery.run.bind(updateCategoryQuery), // TODO: I don't like that the id and the data are in the same object here
	deleteCategory: deleteCategoryQuery.run.bind(deleteCategoryQuery),
};
