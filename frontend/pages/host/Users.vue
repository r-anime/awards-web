<template>
	<div class="section">
		<div>
			<div class="level title-margin">
				<div class="level-left">
					<div class="level-item">
						<h2 class="title">Users</h2>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<div class="field">
							<div class="control is-expanded">
								<input class="input" type="text" v-model="userFilter" placeholder="Filter by username"/>
							</div>
						</div>
					</div>
					<div class="level-item">
						<div class="control">
							<button class="button is-primary" @click="addUserOpen = true">Add User</button>
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

		<modal-generic v-model="addUserOpen">
			<h3 class="title">Add User</h3>
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
		</modal-generic>
	</div>
</template>

<script>
import ModalGeneric from '../../components/ModalGeneric';
export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			users: [],
			addUserOpen: false,
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
					flags: 0,
				}),
			}).then(async response => {
				const json = await response.json();
				if (!response.ok) {
					throw json.error;
				}
				this.users.push(json);
				this.addUserOpen = false;
			}).catch(window.alert);
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
