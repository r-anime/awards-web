<template>
	<div v-if="loaded">
		<form @submit.prevent="saveHMs">
			<div class="section columns is-multiline">
				<hms-field v-for="(hm,index) in items" :key="index"
					:hm="hm" :category="category"
					@toggle="updateData(index, $event)" @delete="deleteHM(index)">
				</hms-field>
			</div>
			<div class="section">
				<button class="button is-primary" @click.prevent="insertField">Add HM</button>
				<button type="submit" class="button is-success"
					:class="{'is-loading': submitting}">Save HMs</button>
			</div>
		</form>
	</div>
	<div v-else>
		Loading ...
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import HmsField from './CategoryHMsField';

export default {
	components: {
		HmsField,
	},
	props: {
		category: Object,
	},
	data () {
		return {
			items: [],
			submitting: false,
			loaded: false,
		};
	},
	computed: {
		...mapState([
			'hms',
		]),
	},
	methods: {
		...mapActions([
			'insertHMs',
			'deleteHMs',
			'getHMs',
		]),
		insertField () {
			this.items.push({
				categoryId: this.category.id,
				name: '',
				writeup: '',
			});
		},
		saveHMs () {
			this.submitting = true;
			const delPromise = new Promise(async (resolve, reject) => {
				try {
					await this.deleteHMs(this.category.id);
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			delPromise.then(async () => {
				try {
					await this.insertHMs({
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
	mounted () {
		const getPromise = new Promise(async (resolve, reject) => {
			try {
				await this.getHMs(this.category.id);
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		getPromise.then(() => {
			for (const hm of this.hms) {
				this.items.push({
					categoryId: this.category.id,
					name: hm.name,
					writeup: hm.writeup,
				});
			}
		});
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
