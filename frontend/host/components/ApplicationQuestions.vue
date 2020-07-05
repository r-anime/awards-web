<template>
	<div v-if="loaded">
		<div v-if="isAdmin" class="section">
			<div class="level title-margin">
				<div class="level-right">
					<div class="level-item">
						<div class="control">
							<button class="button is-primary" @click="createQuestionGroupOpen = true">Create Question Group</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="section columns is-multiline">
			<ApplicationQuestionsField v-for="(questionGroup,index) in items" :key="index"
				:questionGroup="questionGroup"
				:index="index"
				@toggle="updateGroup(index, $event)" @delete="deleteGroup(index)">
			</ApplicationQuestionsField>
		</div>

	<modal-generic v-model="createQuestionGroupOpen">
      <h3 class="title">Create Question Group</h3>
      <form v-if="isAdmin" @submit.prevent="submitCreateQuestionGroup">
        <div class="field">
          <label class="label">Order</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="order"
              placeholder="The order in which this group appears on the app. Lower number appears first."
            />
          </div>
        </div>
		<div class="field">
          <label class="label">Weight</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="weight"
              placeholder="The weight assigned to this question group..."
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
	<div v-else>
		Loading ...
	</div>
</template>

<script>
import {mapActions, mapState, mapGetters} from 'vuex';
import ApplicationQuestionsField from './ApplicationQuestionsField';
import ModalGeneric from '../../common/ModalGeneric';

export default {
	components: {
		ApplicationQuestionsField,
		ModalGeneric,
	},
	props: {
		application: Object,
	},
	data () {
		return {
			items: [],
			submitting: false,
			loaded: false,
			createQuestionGroupOpen: false,
			order: '',
			weight: '',
		};
	},
	computed: {
		...mapState([
			'questionGroups',
		]),
		...mapGetters(['isAdmin']),
	},
	methods: {
		...mapActions([
			'getQuestionGroups',
			'createQuestionGroup',
			'deleteQuestionGroup',
		]),
		async submitCreateQuestionGroup () {
			if (!parseInt(this.order, 10) || !parseFloat(this.weight)) {
				// eslint-disable-next-line no-alert
				alert('Please enter numbers, not strings');
				return;
			}
			this.submitting = true;
			this.createQuestionGroupOpen = true;
			try {
				await this.createQuestionGroup({
					order: parseInt(this.order, 10),
					weight: parseFloat(this.weight),
					app_id: this.application.id,
				});
			} finally {
				this.items = this.questionGroups;
				this.submitting = false;
				this.createQuestionGroupOpen = false;
			}
		},
		updateGroup (index, data) {
			this.items[index] = data;
		},
		deleteGroup (index) {
			this.items.splice(index, 1);
		},
	},
	async mounted () {
		if (!this.questionGroups) {
			await this.getQuestionGroups();
		}
		this.items = this.questionGroups;
		this.loaded = true;
	},
};
</script>
