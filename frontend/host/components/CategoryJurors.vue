<template>
	<div v-if="loaded && !locked">
		<form @submit.prevent="saveJurors">
			<div class="section columns is-multiline">
				<jurors-field v-for="(juror,index) in items" :key="index"
					:juror="juror" :category="category"
					@toggle="updateData(index, $event)" @delete="deleteJuror(index)">
				</jurors-field>
			</div>
			<div class="section">
				<button class="button is-primary" @click.prevent="insertField">Add Juror</button>
				<button type="submit" class="button is-success"
					:class="{'is-loading': submitting}">Save Jurors</button>
			</div>
		</form>
	</div>
	<div v-else-if="locked">
		You cannot see jurors at this time.
	</div>
	<div v-else>
		Loading ...
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import JurorsField from './CategoryJurorsField';

export default {
	components: {
		JurorsField,
	},
	props: {
		category: Object,
	},
	data () {
		return {
			items: [],
			submitting: false,
			loaded: false,
			locked: null,
		};
	},
	computed: {
		...mapState([
			'jurors',
			'locks',
			'me',
		]),
	},
	methods: {
		...mapActions([
			'insertJurors',
			'deleteJurors',
			'getJurors',
			'getLocks',
			'getMe',
		]),
		insertField () {
			this.items.push({
				categoryId: this.category.id,
				name: '/u/topjuror',
				link: '',
			});
		},
		saveJurors () {
			this.submitting = true;
			const delPromise = new Promise(async (resolve, reject) => {
				try {
					await this.deleteJurors(this.category.id);
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			delPromise.then(async () => {
				try {
					await this.insertJurors({
						id: this.category.id,
						data: this.items,
					});
				} finally {
					this.submitting = false;
				}
			});
		},
		updateData (index, data) {
			this.items[index] = data;
		},
		deleteJuror (index) {
			this.items.splice(index, 1);
		},
	},
	async mounted () {
		if (!this.me) {
			await this.getMe();
		}
		if (!this.jurors) {
			await this.getJurors();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		const nameLock = this.locks.find(lock => lock.name === 'app-names');
		if (nameLock.flag || this.me.level > nameLock.level) {
			this.locked = false;
			for (const juror of this.jurors.filter(aJuror => aJuror.categoryId === this.category.id)) {
				this.items.push({
					categoryId: this.category.id,
					name: juror.name,
					link: juror.link,
				});
			}
		} else {
			this.locked = true;
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
