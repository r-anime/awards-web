const queries = {
    showPaginatedQuery: `query ($id: [Int], $page: Int, $perPage: Int) {
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
      }
      `,
    VAPaginatedQuery: `query ($id: [Int], $page: Int, $perPage: Int) {
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
      `
}

export default queries;