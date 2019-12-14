async function makeQuery (query, variables) {
	const response = await fetch('https://graphql.anilist.co', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Accept': 'application/json',
		},
		body: JSON.stringify({query, variables}),
	});
	if (!response.ok) return alert('no bueno');
	const data = await response.json();
	return data.data;
}

module.exports = {
	makeQuery,
};
