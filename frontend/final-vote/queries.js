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
	  userQuery: `query ($userId: String, $page: Int) {
			Page(page: $page, perPage: 50) {
			pageInfo {
				total
				perPage
				currentPage
				lastPage
				hasNextPage
			}
			mediaList(userName: $userId, type: ANIME, sort: [MEDIA_TITLE_ENGLISH], status_in:[CURRENT, COMPLETED]) {
				media {
				id
				title {
					romaji
					english
				}
			  }
			}
		  }
		}	  
	 `,
};

export default queries;
