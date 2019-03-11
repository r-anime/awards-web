<template>
	<table>
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
</template>

<script>
export default {
	data () {
		return {
			users: [
				{name: 'Geo1088', type: 'Moderator'},
				{name: 'ShaKing807', type: 'Moderator'},
			],
		};
	},
};
</script>
