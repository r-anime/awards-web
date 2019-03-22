<template>
	<table v-if="loaded">
		<tr>
			<th v-for="(value, key) in users[0]" :key="key">
				{{key}}
			</th>
		</tr>
		<tr v-for="user in users" :key="user.name">
			<td v-for="(value, key) in user" :key="user.name + key">
				{{value}}
			</td>
		</tr>
	</table>
	<h1 v-else>Loading...</h1>
</template>

<script>
export default {
	data () {
		return {
			users: [],
			loaded: false,
		};
	},
	created () {
		fetch('/api/users').then(res => res.json()).then(users => {
			this.users = users;
		}).catch(error => {
			window.alert(`Couldn't get users.\n${error}`);
		}).finally(() => {
			this.loaded = true;
		});
	},
};
</script>
