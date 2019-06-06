<template>
	<div>
		<h2>Add user</h2>
		<form class="field is-grouped" @submit.prevent="addUser">
			<div class="control">
				<input class="input" type="text" v-model="username" placeholder="Reddit Username"/>
			</div>
			<div class="control">
				<div class="select">
					<select v-model="userLevel">
						<option :value="1">Juror</option>
						<option :value="2">Host</option>
					</select>
				</div>
			</div>
			<div class="control">
				<input class="button is-primary" type="submit" value="Add">
			</div>
		</form>
		<table class="table is-hoverable is-fullwidth">
			<tbody>
			<tr>
				<th>Reddit</th>
				<th>Last Login</th>
				<th>User Level</th>
				<th>Actions</th>
			</tr>
			<tr v-for="user in users" :key="user.reddit">
				<td>/u/{{user.reddit}}</td>
				<td>{{dateDisplay(user.lastLogin)}}</td>
				<td>{{user.level}}</td>
				<td>
					<button class="button is-danger" @click="removeUser(user)">Remove</button>
				</td>
			</tr>
			</tbody>
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
		dateDisplay (time) {
			if (time == null) return 'Never';
			return new Date(time).toLocaleString();
		},
		removeUser (user) {
			fetch(`/api/user/${user.reddit}`, {
				method: 'DELETE',
			}).then(async response => {
				if (!response.ok) {
					return window.alert((await response.json()).error);
				}
				window.alert('User removed.');
				this.users.splice(this.users.indexOf(user), 1);
			});
		},
		addUser () {
			fetch('/api/user', {
				method: 'POST',
				body: JSON.stringify({
					reddit: this.username,
					level: this.userLevel,
				}),
			}).then(async response => {
				if (!response.ok) {
					window.alert((await response.json()).error);
					return;
				}
				this.users.push(await response.json());
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
