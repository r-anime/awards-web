<template>
	<div>
		<h2 class="is-size-4">Add user</h2>
		<form
			v-if="$root.me.level > 1"
			class="field is-grouped"
			@submit.prevent="addUser"
		>
			<div class="control">
				<input class="input" type="text" v-model="username" placeholder="Reddit Username"/>
			</div>
			<div class="control">
				<div class="select">
					<select v-model="userLevel">
						<option :value="1">Juror</option>
						<option v-if="$root.me.level > 2" :value="2">Host</option>
						<option v-if="$root.me.level > 3" :value="3">Mod</option>
					</select>
				</div>
			</div>
			<div class="control">
				<input class="button is-primary" type="submit" value="Add">
			</div>
		</form>
		<div>
			<div class="level">
				<div class="level-left">
					<div class="level-item">
						<h2 class="subtitle is-5">All Users</h2>
					</div>
					<div class="level-item">
						<div class="field">
							<div class="control is-expanded">
								<input class="input" type="text" v-model="userFilter" placeholder="Reddit username"/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<table class="table is-hoverable is-fullwidth">
			<tbody>
			<tr>
				<th>Reddit</th>
				<th>User Level</th>
				<th>Actions</th>
			</tr>
			<tr v-for="user in filteredUsers" :key="user.reddit">
				<td>/u/{{user.reddit}}</td>
				<td>{{levelDisplay(user.level)}}</td>
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
			userFilter: '',
			username: '',
			userLevel: 1,
		};
	},
	computed: {
		filteredUsers () {
			if (!this.userFilter) return this.users;
			return this.users.filter(user => user.reddit.includes(this.userFilter));
		},
	},
	methods: {
		dateDisplay (time) {
			if (time == null) return 'Never';
			return new Date(time).toLocaleString();
		},
		levelDisplay (level) {
			let levelString;
			if (level === 4) {
				levelString = 'Database Admin';
			} else if (level === 3) {
				levelString = 'Moderator';
			} else if (level === 2) {
				levelString = 'Host';
			} else if (level === 1) {
				levelString = 'Juror';
			} else {
				levelString = 'Public';
			}
			return `${level}: ${levelString}`;
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
