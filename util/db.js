const path = require('path');
const bettersqlite3 = require('better-sqlite3');
const config = require('../config');

const db = bettersqlite3(path.join(config.db.path, config.db.filename));

// Define all our queries
const getUserQuery = db.prepare('SELECT * FROM users WHERE reddit=?');
const getAllUsersQuery = db.prepare('SELECT * FROM users');
const insertUserQuery = db.prepare('INSERT INTO users (reddit, level, flags) VALUES (:reddit, :level, :flags)');
const deleteUserQuery = db.prepare('DELETE FROM users WHERE reddit=?');

const getCategoryQuery = db.prepare('SELECT * FROM categories WHERE id=? AND active=1');
const getCategoryByRowidQuery = db.prepare('SELECT * FROM categories WHERE rowid=? AND active=1');
const getAllCategoriesQuery = db.prepare('SELECT * FROM categories WHERE active=1');
const insertCategoryQuery = db.prepare('INSERT INTO categories (name,entryType,entries,awardsGroup) VALUES (:name,:entryType,:entries,:awardsGroup)');
const updateCategoryQuery = db.prepare('UPDATE categories SET name=:name, entryType=:entryType, entries=:entries, awardsGroup=:awardsGroup WHERE id=:id');
const deleteCategoryQuery = db.prepare('UPDATE categories SET active=0 WHERE id=?');

const getCategoryByGroupQuery = db.prepare('SELECT * from categories WHERE active=1 and awardsGroup=?');

const getAllThemesQuery = db.prepare('SELECT * FROM themes');
const insertThemesQuery = db.prepare('INSERT INTO themes (anime,title,themeType,anilistID,themeNo,link) VALUES (:anime,:title,:themeType,:anilistID,:themeNo,:link)');
const deleteThemesQuery = db.prepare('DELETE FROM themes WHERE themeType=?');

const deleteAllVotesFromUserQuery = db.prepare('DELETE FROM votes WHERE reddit_user=?');
const pushUserVotesQuery = db.prepare('INSERT INTO votes (reddit_user,category_id,entry_id) VALUES (:redditUser,:categoryId,:entryId)');
const pushUserThemeVotesQuery = db.prepare('INSERT INTO votes (reddit_user,category_id,entry_id,theme_name,anilist_id) VALUES (:redditUser,:categoryId,:entryId,:themeName,:anilistId)');
const pushUserDashboardVotesQuery = db.prepare('INSERT INTO votes (reddit_user,category_id,entry_id,anilist_id) VALUES (:redditUser,:categoryId,:entryId,:anilistId)');
const getAllUserVotesQuery = db.prepare('SELECT * from votes WHERE reddit_user=?');

const getVoteTotalsQuery = db.prepare('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` GROUP BY `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
const getAllVotesQuery = db.prepare('SELECT * FROM votes');

// Purely for debugging and data purging reasons
const deleteAllVotesQuery = db.prepare('DELETE FROM votes');

module.exports = {
	getUser: getUserQuery.get.bind(getUserQuery),
	getAllUsers: getAllUsersQuery.all.bind(getAllUsersQuery),
	insertUser: insertUserQuery.run.bind(insertUserQuery),
	deleteUser: deleteUserQuery.run.bind(deleteUserQuery),

	getCategory: getCategoryQuery.get.bind(getCategoryQuery),
	getCategoryByRowid: getCategoryByRowidQuery.get.bind(getCategoryByRowidQuery),
	getAllCategories: getAllCategoriesQuery.all.bind(getAllCategoriesQuery),
	insertCategory: insertCategoryQuery.run.bind(insertCategoryQuery),
	updateCategory: updateCategoryQuery.run.bind(updateCategoryQuery),
	deleteCategory: deleteCategoryQuery.run.bind(deleteCategoryQuery),

	getCategoryByGroup: getCategoryByGroupQuery.all.bind(getCategoryByGroupQuery),

	getAllThemes: getAllThemesQuery.all.bind(getAllThemesQuery),
	insertThemes: insertThemesQuery.run.bind(insertThemesQuery),
	deleteThemes: deleteThemesQuery.run.bind(deleteThemesQuery),

	deleteAllVotesFromUser: deleteAllVotesFromUserQuery.run.bind(deleteAllVotesFromUserQuery),
	pushUserVotes: pushUserVotesQuery.run.bind(pushUserVotesQuery),
	pushUserThemeVotes: pushUserThemeVotesQuery.run.bind(pushUserThemeVotesQuery),
	pushUserDashboardVotes: pushUserDashboardVotesQuery.run.bind(pushUserDashboardVotesQuery),

	getAllUserVotes: getAllUserVotesQuery.all.bind(),
	getVoteTotals: getVoteTotalsQuery.all.bind(getVoteTotalsQuery),
	getAllVotes: getAllVotesQuery.all.bind(getAllVotesQuery),
	deleteAllVotes: deleteAllVotesQuery.run.bind(deleteAllVotesQuery),
};
