const maxAccountDate = 0; // push it back to 1578009600 when heather's account is old enough
const eligibilityStart = new Date(2020, 1, 13); // Year, Month, Day
const eligibilityEnd = new Date(2021, 1, 12);

// Blacklist of all the unsubbed anime, note for next year: make this array separate for shows and other stuff
const blacklist = [];

const splitCours = [];

const allBlacklist = blacklist.concat(splitCours);

module.exports = {
	maxAccountDate,
	eligibilityStart,
	eligibilityEnd,
	blacklist,
	splitCours,
	allBlacklist,
};
