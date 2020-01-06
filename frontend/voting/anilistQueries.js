// Remember to fix the eligibility period on these two queries and these variables.

const eligibilityStart = new Date(2019, 1, 8); // Year, Month, Day
const eligibilityEnd = new Date(2020, 1, 12);

// Blacklist of all the unsubbed anime, note for next year: make this array separate for shows and other stuff
const blacklist = [
	101115,
	102005,
	101228,
	108734,
	106607,
	110124,
	110201,
	109931,
	104562,
	107858,
	107860,
	107859,
	104329,
	100269,
	107908,
	101992,
	107012,
	99425,
	103221,
	98784,
	107252,
	107864,
	107208,
	102976,
	106286,
	109190,
	105143,
	106240,
	107727,
	108735,
	107905,
	108553,
	100675,
	108577,
	108626,
	108487,
	101610,
	110413,
	111290,
	112732,
	112308,
	105596,
	111905,
	111944,
	104307,
	101425,
	102927,
	101555,
	99720,
	21778,
	111789,
	110261,
	113655,
	107203,
	108260,
	110734,
	109215,
	113273,
	107624,
	104324,
	105907,
	109931,
	98056,
	107857,
	107208,
	107202,
	112135,
	107905,
	101344,
	100211,
	112522,
	101579,
	110451,
	113993,
	108039,
	102801,
	108767,
	99995,
	113668,
	112026,
	112152,
	108728,
];

const splitCours = [
	107447,
	110229,
	108810,
	109562,
	100781,
	100782,
	111306,
	104307,
	102346,
	102347,
	102348,
	102349,
	102350,
	109670,
	108937,
	111048,
	108891,
	106862,
	110173,
	111129,
	110738,
	109819,
	113924,
	104580,
	108759,
	107506,
	105245,
	107138,
	110382,
	102976,
	112480,
	114195,
	112019,
	107880,
	100782,
	110131,
	106240,
	112472,
	107692,
	109280,
	108811,
	104104,
	113302,
	105893,
	108942,
];

const allBlacklist = blacklist.concat(splitCours);

const showQuery = `query ($search: String, $blacklist: [Int]) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, id_not_in: $blacklist, sort: [SEARCH_MATCH], isAdult: false, search: $search, format_in: [TV, ONA, OVA, SPECIAL], endDate_greater: 20190108, endDate_lesser: 20200112, duration_greater: 15, episodes_greater: 1, countryOfOrigin: JP) {
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

const prodQuery = `query ($search: String, $blacklist: [Int]) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, id_not_in: $blacklist, search: $search, sort: [SEARCH_MATCH], isAdult: false, format_in: [TV, TV_SHORT, ONA, OVA, SPECIAL], endDate_greater: 20190108, endDate_lesser: 20200112, episodes_greater: 1, countryOfOrigin: JP) {
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

const testQuery = `query ($search: String, $blacklist: [Int]) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, id_not_in: $blacklist, sort: [SEARCH_MATCH], format_in: [TV, TV_SHORT, ONA, OVA, SPECIAL, MOVIE], search: $search, endDate_greater: 20190108, endDate_lesser: 20200112, isAdult: false, countryOfOrigin: JP) {
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

const showQuerySimple = `query ($id: [Int], $page: Int, $perPage: Int) {
	Page (page: $page, perPage: $perPage) {
	  pageInfo {
		total
		currentPage
		lastPage
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

const charQuerySimple = `query ($id: [Int], $page: Int, $perPage: Int) {
	Page (page: $page, perPage: $perPage) {
	  pageInfo {
		total
		currentPage
		lastPage
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
  }`;

module.exports = {
	showQuery,
	showQuerySimple,
	charQuery,
	charQuerySimple,
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
	splitCours,
	allBlacklist,
};
