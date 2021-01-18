<template>
  <div class="section">
    <div class="level title-margin">
      <div class="level-left">
        <div class="level-item">
          <h2 class="title">Sort Categories</h2>
        </div>
      </div>
      <div class="level-right">
        <div class="level-item">
          <div class="field">
          </div>
        </div>
        <div v-if="isHost" class="level-item">
          <div class="control">
            <button class="button is-primary" :class="{'is-loading': saving}" @click="updateSort" :disabled="saving">Save</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loaded">Loading categories...</div>
    <div v-else-if="categories.length === 0">No categories!</div>
    <div v-else class="">
		<draggable v-model="orderableCats">
			<transition-group name="flip-list">
				<div
				class="draggable-cat"
				v-for="category in orderableCats"
				:key="category.id"
				>
					<div class="notification">{{category.name}}</div>
				</div>
			</transition-group>
		</draggable>
    </div>
  </div>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import draggable from 'vuedraggable'

export default {
	components: {
		draggable,
	},
	data () {
		return {
			orderableCats: {},
			loaded: false,
			saving: false,
		};
	},
	computed: {
		// Pull in stuff from Vuex
		...mapState([
			'categories',
		]),
		...mapGetters(['isHost']),
	},
	methods: {
		...mapActions([
			'getCategories',
			'updateCategories',
		]),
		async updateSort(){
			this.saving = true;
			await this.updateCategories(this.orderableCats);
			this.saving = false;
		}
	},
	async mounted () {
		if (!this.categories) {
			await this.getCategories();
		}
		this.orderableCats = this.categories;
		// console.log(this.orderableCats);
		this.loaded = true;
	},
};
</script>
<style scoped>
.flip-list-move {
	transition: transform 0.3s;
}
.draggable-cat {
	margin-bottom: 0.5rem;
}
</style>