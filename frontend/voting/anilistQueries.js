// Remember to fix the eligibility period on these two queries and these variables.

const eligibilityStart = new Date(2019, 1, 1); // Year, Month, Day
const eligibilityEnd = new Date(2020, 1, 1);

const showQuery = `query ($search: String) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, search: $search, format_in: [TV, ONA, OVA, SPECIAL], endDate_greater: 20190000, endDate_lesser: 20199999, duration_greater: 15, episodes_greater: 1) {
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
	  results: media(type: ANIME, search: $search, endDate_greater: 20190000, endDate_lesser: 20199999) {
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
		results: media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
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

module.exports = {
	showQuery,
	charQuery,
	vaQuery,
	testQuery,
	eligibilityStart,
	eligibilityEnd,
};