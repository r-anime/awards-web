function isOPED (category) {
	if (category.entryType === 'themes' && !category.name.includes('OST')) return true;
	return false;
}

function isDashboard (category) {
	if (category.awardsGroup === 'genre') {
		return true;
	} else if (category.awardsGroup === 'main' && category.name !== 'Anime of the Year') {
		return true;
	} else if (category.awardsGroup === 'test' && category.name.includes('Sports')) {
		return true;
	} else if (category.name.includes('OST') && category.awardsGroup === 'production') {
		return true;
	}
	return false;
}

module.exports = {
	isOPED,
	isDashboard,
};
