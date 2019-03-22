<template>
	<div v-if="loaded">
		<div class="filter">
			Filter:
			<input v-model="filter" placeholder="/u/redditname, @Discord Name"/>
		</div>
		<table>
			<tr>
				<th v-for="(value, key) in users[0]" :key="key">
					{{key}}
				</th>
			</tr>
			<tr v-for="user in filteredUsers" :key="user.name">
				<td v-for="(value, key) in user" :key="user.name + key">
					{{value}}
				</td>
			</tr>
		</table>
	</div>
	<h1 v-else>Loading...</h1>
</template>

<script>
export default {
	data () {
		return {
			users: [],
			loaded: false,
			filter: '',
		};
	},
	computed: {
		filteredUsers () {
			if (!this.filter) return this.users;
			return this.users.filter(user => `/u/${user.reddit}`.toLowerCase().includes(this.filter));
		},
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
