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
              <input
                class="input"
                type="text"
                v-model="categoryFilter"
                placeholder="Filter by name"
              />
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

    <div v-if="!loaded">Loading categories...</div>
    <div v-else-if="categories.length === 0">No categories!</div>
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
            >{{category.name}}</router-link>
          </h3>
          <div class="level is-mobile">
            <div class="level-left">
              <div class="level-item">
                <span class="tag">{{categoryEntryType(category)}}</span>
              </div>
              <div class="level-item">
                <span class="tag">{{categoryGroup(category)}}</span>
              </div>
              <div class="level-item">
                <router-link
                  :to="{name: 'categoryEntries', params: {categoryId: category.id}}"
                  class="tag"
                >{{categoryEntryCount(category)}}</router-link>
              </div>
            </div>
          </div>
          <div class="level is-mobile">
            <div class="level-left"></div>
            <div class="level-right">
              <div class="level-item">
                <button
                  class="button is-danger"
                  v-bind:class="{'is-loading' : deleting && category.id === selectedCategoryId}"
                  @click="submitDeleteCategory(category.id)"
                >Delete</button>
              </div>
              <div class="level-item">
                <button
                  class="button is-info"
                  v-bind:class="{'is-loading' : duplicating && category.id === selectedCategoryId}"
                  @click="submitDuplicateCategory(category.id, category.name, category.entryType, category.awardsGroup, category.jurorCount, category.description)"
                >Copy</button>
              </div>
              <div class="level-item">
                <router-link
                  :to="{name: 'category', params: {categoryId: category.id}}"
                  class="button is-info"
                >View</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <modal-generic v-model="createCategoryOpen">
      <h3 class="title">Create Category</h3>
      <form v-if="isHost" @submit.prevent="submitCreateCategory">
        <div class="field">
          <label class="label">Name</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="categoryName"
              placeholder="Action, Animation, AotY..."
            />
          </div>
        </div>
        <div class="field">
          <label class="label">Entry Type</label>
          <div class="control">
            <div class="select">
              <select v-model="newEntryType">
                <option disabled>Select one</option>
                <option value="shows">Shows</option>
                <option value="characters">Characters</option>
                <option value="vas">VA Performances</option>
                <option value="themes">OPs/EDs</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="label">Awards Group</label>
          <div class="control">
            <div class="select">
              <select v-model="newEntryGroup">
                <option disabled>Select one</option>
                <option value="genre">Genre Awards</option>
                <option value="character">Character Awards</option>
                <option value="production">Production Awards</option>
                <option value="test">Test Category</option>
                <option value="main">Main Awards</option>
              </select>
            </div>
          </div>
        </div>
		<div class="field">
          <label class="label">Description</label>
          <div class="control">
				<input
				class="input"
				type="text"
				v-model="newDescription"
				placeholder="A description of what the category entails"
				/>
          </div>
        </div>
		<div class="field">
          <label class="label">Juror Count</label>
          <div class="control">
				<input
				class="input"
				type="text"
				v-model="newJurorCount"
				placeholder="Number of jurors in the category"
				/>
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
import ModalGeneric from '../../common/ModalGeneric';

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
			newEntryType: 'shows',
			newEntryGroup: 'genre',
			newJurorCount: '',
			newDescription: '',
			submitting: false,
			deleting: false,
			duplicating: false,
			selectedCategoryId: '',
			loaded: false,
		};
	},
	computed: {
		// Pull in stuff from Vuex
		...mapState([
			'categories',
			'entries',
		]),
		...mapGetters(['isHost']),
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
			'getEntries',
		]),
		categoryEntryCount (category) {
			const categoryEntries = this.entries.filter(entry => entry.categoryId === category.id);
			if (categoryEntries.length === 0) return 'No entries';
			return `${categoryEntries.length} entr${categoryEntries.length === 1 ? 'y' : 'ies'}`;
		},
		categoryEntryType (category) {
			switch (category.entryType) {
				case 'shows':
					return 'Shows';
				case 'characters':
					return 'Characters';
				case 'vas':
					return 'VA Performances';
				case 'themes':
					return 'OPs/EDs';
				default:
					return 'Unknown entry type';
			}
		},
		categoryGroup (category) {
			switch (category.awardsGroup) {
				case 'genre':
					return 'Genre Awards';
				case 'character':
					return 'Character Awards';
				case 'production':
					return 'Production Awards';
				case 'test':
					return 'Test Category';
				case 'main':
					return 'Main Awards';
				default:
					return 'Unknown Group';
			}
		},
		submitDeleteCategory (categoryID) {
			this.deleting = true;
			this.selectedCategoryId = categoryID;
			setTimeout(async () => {
				try {
					await this.deleteCategory(categoryID);
				} finally {
					this.deleting = false;
				}
			});
		},
		submitDuplicateCategory (
			categoryID,
			categoryName,
			categoryType,
			categoryGroup,
			categoryJurorCount,
			categoryDescription,
		) {
			this.duplicating = true;
			this.selectedCategoryId = categoryID;
			const newName = `${categoryName} copy`;
			setTimeout(async () => {
				try {
					await this.createCategory({
						data: {
							name: newName,
							entryType: categoryType,
							awardsGroup: categoryGroup,
							description: categoryDescription,
							jurorCount: categoryJurorCount,
						},
					});
				} finally {
					this.duplicating = false;
				}
			});
		},
		submitCreateCategory () {
			this.createCategoryOpen = true;
			this.submitting = true;
			setTimeout(async () => {
				try {
					await this.createCategory({
						data: {
							name: this.categoryName,
							entryType: this.newEntryType,
							awardsGroup: this.newEntryGroup,
							description: this.newDescription,
							jurorCount: parseInt(this.newJurorCount, 10),
						},
					});
				} finally {
					this.createCategoryOpen = false;
					this.submitting = false;
				}
			});
		},
	},
	async mounted () {
		if (!this.categories) {
			await this.getCategories();
		}
		if (!this.entries) {
			await this.getEntries();
		}
		this.loaded = true;
	},
};
</script>
