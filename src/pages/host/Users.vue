<template>
	<div>
		<h2>Add user</h2>
		Username:
		<input type="text" v-model="username" placeholder="Reddit Username"/>
		Account level:
		<select v-model="userLevel">
			<option :value="1">Juror</option>
			<option :value="2">Host</option>
		</select>
		<button @click="addUser">Add</button>
		<table>
			<tr>
				<th>Reddit</th>
			</tr>
			<tr v-for="user in users" :key="user.reddit">
				<td>{{user.reddit}}</td>
			</tr>
		</table>
	</div>
</template>

<script>
export default {
	data () {
		return {
			users: [],
			username: '',
			userLevel: 1,
		};
	},
	methods: {
		addUser () {
			fetch('/api/users', {
				method: 'POST',
				body: JSON.stringify({
					redditName: this.username,
					level: this.userLevel,
				}),
			});
		},
	},
	mounted () {
		fetch('/api/users').then(async response => {
			console.log('got users');
			this.users = await response.json();
			console.log(this.users);
		});
	},
};
</script>
