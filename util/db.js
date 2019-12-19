const path = require('path');
const bettersqlite3 = require('better-sqlite3');
const config = require('../config');

const db = bettersqlite3(path.join(config.db.path, config.db.filename));

// Initial setup of the database
// eslint-disable-next-line multiline-comment-style
/*
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
		entryType
			TEXT
			NOT NULL
			CHECK(entryType in ('shows', 'characters', 'vas', 'themes'))
			DEFAULT 'shows',
		entries
			TEXT
			NOT NULL
			DEFAULT ''
	);
	CREATE TABLE IF NOT EXISTS themes (
		id
			INTEGER
			PRIMARY KEY
			AUTOINCREMENT,
		anime
			TEXT
			NOT NULL
			DEFAULT '',
		title
			TEXT,
		themeType
			TEXT
			NOT NULL
			DEFAULT '',
		anilistID
			INTEGER
			NOT NULL,
		themeNo
			TEXT,
		link
			TEXT
			DEFAULT ''
	);
`);
*/

// Define all our queries
const getUserQuery = db.prepare('SELECT * FROM users WHERE reddit=?');
const getAllUsersQuery = db.prepare('SELECT * FROM users');
const insertUserQuery = db.prepare('INSERT INTO users (reddit, level, flags) VALUES (:reddit, :level, :flags)');
const deleteUserQuery = db.prepare('DELETE FROM users WHERE reddit=?');

const getCategoryQuery = db.prepare('SELECT * FROM categories WHERE id=? AND active=1');
const getCategoryByRowidQuery = db.prepare('SELECT * FROM categories WHERE rowid=? AND active=1');
const getAllCategoriesQuery = db.prepare('SELECT * FROM categories WHERE active=1');
const insertCategoryQuery = db.prepare('INSERT INTO categories (name,entryType,entries) VALUES (:name,:entryType,:entries)');
const updateCategoryQuery = db.prepare('UPDATE categories SET name=:name, entryType=:entryType, entries=:entries WHERE id=:id');
const deleteCategoryQuery = db.prepare('UPDATE categories SET active=0 WHERE id=?');

const getAllThemesQuery = db.prepare('SELECT * FROM themes');
const insertThemesQuery = db.prepare('INSERT INTO themes (anime,title,themeType,anilistID,themeNo,link) VALUES (:anime,:title,:themeType,:anilistID,:themeNo,:link)');
const deleteThemesQuery = db.prepare('DELETE FROM themes WHERE themeType=?');

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

	getAllThemes: getAllThemesQuery.all.bind(getAllThemesQuery),
	insertThemes: insertThemesQuery.run.bind(insertThemesQuery),
	deleteThemes: deleteThemesQuery.run.bind(deleteThemesQuery),
};
