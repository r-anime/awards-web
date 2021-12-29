const showQuery = `query ($search: String) {
	anime: Page(page: 1, perPage: 50) {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, sort: [SEARCH_MATCH], isAdult: false, search: $search, format_in: [TV, ONA, OVA, SPECIAL], endDate_greater: 20190108, endDate_lesser: 20200112, duration_greater: 15, episodes_greater: 1, countryOfOrigin: JP) {
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
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 50) {
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
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 50) {
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
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 1) {
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
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 1) {
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
		}
		synonyms
		coverImage {
		  large
		  extraLarge
		}
		siteUrl
		idMal
	  }
	}
  }`;

const charQuerySimple = `query ($id: [Int], $page: Int, $perPage: Int) {
	Page(page: $page, perPage: $perPage) {
	  pageInfo {
		total
		currentPage
		lastPage
	  }
	  results: characters(id_in: $id) {
		id
		name {
		  full
		  alternative
		}
		image {
		  large
		}
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 1) {
		  nodes {
			id
			title {
			  romaji
			  english
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
			  }
			}
		  }
		}
		siteUrl
	  }
	}
  }
  `;

const showQuerySmall = `query ($id: [Int], $page: Int, $perPage: Int) {
	Page(page: $page, perPage: $perPage) {
	  pageInfo {
		lastPage
	  }
	  results: media(type: ANIME, id_in: $id) {
		id
		format
		title {
		  romaji
		  english
		}
		synonyms
		coverImage {
		  large
		}
		siteUrl
		idMal
	  }
	}
  }`;

const charQuerySmall = `query ($id: [Int], $page: Int, $perPage: Int) {
	Page(page: $page, perPage: $perPage) {
	  pageInfo {
		lastPage
	  }
	  results: characters(id_in: $id) {
		id
		name {
		  full
		  alternative
		}
		image {
		  large
		}
		media(sort: [START_DATE], type: ANIME, page: 1, perPage: 5) {
		  edges {
			node {
			  id
			  title {
				romaji
				english
			  }
			  startDate {
				year
				month
				day
			  }
			}
		  }
		}
		siteUrl
	  }
	}
  }`;

const showQueryCache = `query ($page: Int, $perPage: Int, $edlow: FuzzyDateInt, $edhigh: FuzzyDateInt) {
	Page(page: $page, perPage: $perPage) {
	  pageInfo {
			currentPage
      lastPage
	  }
	  results: media(type: ANIME, endDate_greater: $edlow, endDate_lesser: $edhigh, format_in: [TV, TV_SHORT, MOVIE, ONA, OVA, SPECIAL, MUSIC], countryOfOrigin: JP) {
      id
      format
      startDate{
        year
        month
        day
      }
      endDate {
        year
        month
        day
      }
      title {
        romaji
        english
      }
      synonyms
      coverImage {
        large
      }
      siteUrl
      idMal
	  }
	}
}`;

const charQueryByShow = `query ($id: Int, $page: Int, $perPage: Int) {
	Media(id: $id) {
	  id
	  characters(page: $page, perPage: $perPage) {
		pageInfo {
		  currentPage
		  lastPage
		}
		edges {
		  node {
			id
			name {
			  full
			}
			image {
			  large
			}
		  }
		  voiceActors(language:JAPANESE) {
			id
			name {
			  full
			}
			image {
			  large
			}
		  }
		}
	  }
	}
  }
`;

module.exports = {
	showQuery,
	showQueryCache,
	showQuerySimple,
	charQuery,
	charQuerySimple,
	charQueryByShow,
	vaQuery,
	showByIDQuery,
	charByIDQuery,
	vaByIDQuery,
	showQuerySmall,
	charQuerySmall,
};
