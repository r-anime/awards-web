// Remember to fix the eligibility period on these two queries and these variables.

const eligibilityStart = new Date(2019, 1, 8); // Year, Month, Day
const eligibilityEnd = new Date(2020, 1, 12);

// Blacklist of all the unsubbed anime, note for next year: make this array separate for shows and other stuff
const blacklist = [101115, 102005, 101228, 108734, 106607, 110124, 110201, 109931, 104562, 107858, 107860, 107859, 104329, 100269, 107908, 101992, 107012, 99425, 103221, 98784, 107252, 107864, 107208, 105018, 102976, 106286, 109190, 105143, 106240, 107727, 108735, 107905, 108553, 100675, 108577, 108626, 108487, 101610, 110413, 111290, 112732, 112308, 105596, 111905];

const showQuery = `query ($search: String) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, sort:[SEARCH_MATCH], isAdult:false, search: $search, format_in: [TV, ONA, OVA, SPECIAL], endDate_greater: 20190108, endDate_lesser: 20200112, duration_greater: 15, episodes_greater: 1, countryOfOrigin:JP) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }`;

const prodQuery = `query ($search: String) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, search: $search, sort:[SEARCH_MATCH], isAdult: false, format_in: [TV, TV_SHORT, ONA, OVA, SPECIAL], endDate_greater: 20190108, endDate_lesser: 20200112, episodes_greater: 1, countryOfOrigin:JP) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }`;

const testQuery = `query ($search: String) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, sort:[SEARCH_MATCH], format_in: [TV, TV_SHORT, ONA, OVA, SPECIAL, MOVIE],search: $search, endDate_greater: 20190108, endDate_lesser: 20200112, isAdult: false, countryOfOrigin:JP) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }
  `;

// The catch here is that there's literally no filtering on these. The endDate object needs to be filtered client-side for both these queries.
// Essentially the check is super weird to perform. JS's Date() object can work. One of these needs to be the beginning of the eligibility criteria
// and the other needs to be the end. You create another date object for the character or VA and check if it lies between and filter by that.
const charQuery = `query ($search: String) {
	character: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: characters(search: $search, sort: [SEARCH_MATCH]) {
		id
		name {
		  full
		  alternative
		  native
		}
		image {
		  large
		}
		media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 50) {
		  nodes {
			id
			title {
			  romaji
			  english
			  native
			  userPreferred
			}
			endDate {
			  year
			  month
			  day
			}
		  }
		  edges {
			id
			characterRole
		  }
		}
		siteUrl
	  }
	}
  }`;

const vaQuery = `query ($search: String) {
	character: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: characters(search: $search, sort: [SEARCH_MATCH]) {
		id
		name {
		  full
		}
		image {
		  large
		}
		media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 50) {
		  nodes {
			id
			title {
			  romaji
			  english
			  native
			  userPreferred
			}
			endDate {
			  year
			  month
			  day
			}
		  }
		  edges {
			id
			node {
			  id
			}
			characterRole
			voiceActors(language: JAPANESE) {
			  id
			  name {
				full
				alternative
				native
			  }
			}
		  }
		}
		siteUrl
	  }
	}
  }
  `;

const showByIDQuery = `query ($id: [Int]) {
	Page {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, id_in: $id) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }`;

const charByIDQuery = `query ($id: [Int]) {
	Page {
	  pageInfo {
		total
	  }
	  results: characters(id_in: $id) {
		id
		name {
		  full
		  alternative
		  native
		}
		image {
		  large
		}
		media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
		  nodes {
			id
			title {
			  romaji
			  english
			  native
			  userPreferred
			}
			endDate {
			  year
			  month
			  day
			}
		  }
		  edges {
			id
			characterRole
		  }
		}
		siteUrl
	  }
	}
  }
  `;

const vaByIDQuery = `query ($id: [Int]) {
	Page {
	  pageInfo {
		total
	  }
	  results: characters(id_in: $id) {
		id
		name {
		  full
		}
		image {
		  large
		}
		media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
		  nodes {
			id
			title {
			  romaji
			  english
			  native
			  userPreferred
			}
			endDate {
			  year
			  month
			  day
			}
		  }
		  edges {
			id
			node {
			  id
			}
			characterRole
			voiceActors(language: JAPANESE) {
			  id
			  name {
				full
				alternative
				native
			  }
			}
		  }
		}
		siteUrl
	  }
	}
  }
  `;

const themeByIDQuery = `query ($id: [Int]) {
	Page {
	  results: media(id_in: $id) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }
  `;

module.exports = {
	showQuery,
	charQuery,
	vaQuery,
	testQuery,
	prodQuery,
	eligibilityStart,
	eligibilityEnd,
	showByIDQuery,
	charByIDQuery,
	vaByIDQuery,
	themeByIDQuery,
	blacklist,
};
