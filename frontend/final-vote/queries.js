const queries = {
	showPaginatedQuery: `query ($id: [Int], $page: Int, $perPage: Int) {
		Page(page: $page, perPage: $perPage) {
		  pageInfo {
			lastPage
		  }
		  results: media(type: ANIME, id_in: $id) {
			id
			title {
			  romaji
			  english
			}
			coverImage {
			  large
			}
		  }
		}
	  }`,
	charPaginatedQuery: `query ($id: [Int], $page: Int, $perPage: Int) {
		Page(page: $page, perPage: $perPage) {
		  pageInfo {
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
			media(sort: [END_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
			  nodes {
				id
				title {
				  romaji
				  english
				}
			  }
			  edges {
				id
				node {id}
				voiceActors (language:JAPANESE) {
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
	  }`,
};

export default queries;
