<template>
	<div class="section">
		<div class="level title-margin">
			<div class="level-left">
				<div class="level-item">
					<h2 class="title">Categories</h2>
				</div>
			</div>
			<div class="level-right">
				<div class="level-item">
					<div class="field">
						<div class="control is-expanded">
							<input class="input" type="text" v-model="categoryFilter" placeholder="Filter by name"/>
						</div>
					</div>
				</div>
				<div v-if="isHost" class="level-item">
					<div class="control">
						<button class="button is-primary" @click="createCategoryOpen = true">Create Category</button>
					</div>
				</div>
			</div>
		</div>

		<div v-if="!categories">
			Loading categories...
		</div>
		<div v-else-if="categories.length === 0">
			No categories!
		</div>
		<div v-else class="columns is-mobile is-multiline">
			<div
				class="column is-half-tablet is-one-third-desktop"
				v-for="category in filteredCategories"
				:key="category.id"
			>
				<div class="box">
					<h3 class="title is-4">
						<router-link
							:to="{name: 'category', params: {categoryId: category.id}}"
							class="has-text-dark"
						>
							{{category.name}}
						</router-link>
					</h3>
					<div class="level is-mobile">
						<div class="level-left">
							<div class="level-item">
								<span class="tag">{{categoryEntryType(category)}}</span>
							</div>
							<div class="level-item">
								<router-link
									:to="{name: 'categoryEntries', params: {categoryId: category.id}}"
									class="tag"
								>
									{{categoryEntryCount(category)}}
								</router-link>
							</div>
						</div>
						<div class="level-right">
							<div class="level-item">
								<button class="button is-danger"
								v-bind:class="{'is-loading' : deleting && category.id === selectedCategoryId}"
								@click="submitDeleteCategory(category.id)">
								Remove
								</button>
							</div>
							<div class="level-item">
								<router-link
									:to="{name: 'category', params: {categoryId: category.id}}"
									class="button is-info"
								>
									View
								</router-link>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<modal-generic v-model="createCategoryOpen">
			<h3 class="title">Create Category</h3>
			<form
				v-if="isHost"
				@submit.prevent="submitCreateCategory"
			>
				<div class="field">
					<div class="control">
						<input class="input" type="text" v-model="categoryName" placeholder="Genre Awards"/>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<button class="button is-primary" :class="{'is-loading' : submitting}" type="submit">Add</button>
					</div>
				</div>
			</form>
		</modal-generic>
	</div>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import ModalGeneric from '../../components/ModalGeneric';

export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			// A string to filter the displayed list
			categoryFilter: '',
			// Info for the "New Category" modal
			createCategoryOpen: false,
			categoryName: '',
			submitting: false,
			deleting: false,
			selectedCategoryId: '',
		};
	},
	computed: {
		// Pull in stuff from Vuex
		...mapState([
			'categories',
		]),
		...mapGetters([
			'isHost',
		]),
		filteredCategories () {
			if (!this.categoryFilter) return this.categories;
			return this.categories.filter(cat => cat.name.toLowerCase().includes(this.categoryFilter.toLowerCase()));
		},
	},
	methods: {
		...mapActions([
			'getCategories',
			'createCategory',
			'deleteCategory',
		]),
		categoryEntryCount (category) {
			if (!category.entries) return 'No entries';
			const entries = JSON.parse(category.entries);
			if (!entries || !entries.length) return 'No entries';
			return `${entries.length} entr${entries.length === 1 ? 'y' : 'ies'}`;
		},
		categoryEntryType (category) {
			switch (category.entryType) {
				case 'shows': return 'Shows';
				case 'characters': return 'Characters';
				case 'vas': return 'VA Performances';
				case 'themes': return 'OPs/EDs';
				default: return 'Unknown entry type';
			}
		},
		submitDeleteCategory(categoryID) {
			this.deleting = true;
			this.selectedCategoryId = categoryID;
			setTimeout(async () => {
				try {
					await this.deleteCategory(categoryID);
				}
				finally {
					this.deleting = false;
				}
			});
		},
		submitCreateCategory () {
			this.createCategoryOpen = true;
			this.submitting = true;
			setTimeout(async () => {
				try {
					await this.createCategory({
						name: this.categoryName
					});
				}
				finally {
					this.createCategoryOpen = false;
					this.submitting = false;
				}
			});
		},
	},
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
	},
};
</script>
