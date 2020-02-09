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

const getNominationsByCategoryQuery = db.prepare('SELECT * FROM nominations WHERE category_id=? AND active=1');
const insertNominationQuery = db.prepare('INSERT INTO nominations (category_id, anilist_id, character_id, theme_id, entry_type, active, writeup, rank, votes, finished, alt_name, staff) VALUES (:categoryID, :anilistID, :characterID, :themeID, :entryType, :active, :writeup, :juryRank, :publicVotes, :publicSupport, :altName, :staff)');
const deactivateNominationsByCategoryQuery = db.prepare('UPDATE `nominations` SET `active`=0 WHERE `category_id`=?');
const toggleActiveNominationsByIdQuery = db.prepare('UPDATE nominations SET active=:active WHERE id=:id');
const getNominationByRowIdQuery = db.prepare('SELECT * from nominations WHERE id=? AND active=1');
const getAllNominationsQuery = db.prepare('SELECT * from nominations');

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
const getDashboardTotalsQuery = db.prepare('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`anilist_id` IS NOT NULL AND `votes`.`theme_name` IS NULL GROUP BY `votes`.`category_id`, `votes`.`anilist_id` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
const getOPEDTotalsQuery = db.prepare('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`theme_name` IS NOT NULL GROUP BY `votes`.`category_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
const getVoteUserCountQuery = db.prepare('SELECT COUNT(DISTINCT `reddit_user`) as `count` FROM `votes`');
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

	getAllUserVotes: getAllUserVotesQuery.all.bind(getAllUserVotesQuery),
	getVoteUserCount: getVoteUserCountQuery.all.bind(getVoteUserCountQuery),
	getVoteTotals: getVoteTotalsQuery.all.bind(getVoteTotalsQuery),
	getDashboardTotals: getDashboardTotalsQuery.all.bind(getDashboardTotalsQuery),
	getOPEDTotals: getOPEDTotalsQuery.all.bind(getOPEDTotalsQuery),

	getAllVotes: getAllVotesQuery.all.bind(getAllVotesQuery),
	deleteAllVotes: deleteAllVotesQuery.run.bind(deleteAllVotesQuery),

	getNominationsByCategory: getNominationsByCategoryQuery.all.bind(getNominationsByCategoryQuery),
	insertNomination: insertNominationQuery.run.bind(insertNominationQuery),
	deactivateNominationsByCategory: deactivateNominationsByCategoryQuery.run.bind(deactivateNominationsByCategoryQuery),
	toggleActiveNominationsById: toggleActiveNominationsByIdQuery.run.bind(toggleActiveNominationsByIdQuery),
	getNominationByRowId: getNominationByRowIdQuery.get.bind(getNominationByRowIdQuery),
	getAllNominations: getAllNominationsQuery.all.bind(getAllNominationsQuery),
};
