const bettersqlite3 = require('better-sqlite3');
const config = require('../config');

const db = bettersqlite3(config.db.location);

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

const getUserQuery = db.prepare('SELECT * FROM users WHERE reddit=?');
const getAllUsersQuery = db.prepare('SELECT * FROM users');
const insertUserQuery = db.prepare('INSERT INTO users (reddit, flags) VALUES (:reddit, :flags)');
const deleteUserQuery = db.prepare('DELETE FROM users WHERE reddit=?');

const getCategoryQuery = db.prepare('SELECT * FROM categories WHERE id=?');
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
	getAllCategories: getAllCategoriesQuery.all.bind(getAllCategoriesQuery),
	insertCategory: insertCategoryQuery.run.bind(insertCategoryQuery),
	updateCategory: updateCategoryQuery.run.bind(updateCategoryQuery),
	deleteCategory: deleteCategoryQuery.run.bind(deleteCategoryQuery),
};
