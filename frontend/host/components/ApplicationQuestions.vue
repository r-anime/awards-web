<template>
	<div v-if="loaded">
		<div class="section">
			<div class="level title-margin">
				<div class="level-left">
					<div class="level-item">
						<div class="control">
							<h2 class="title is-3">Questions</h2>
						</div>
					</div>
				</div>
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
      <form @submit.prevent="submitCreateQuestionGroup">
		<div class="field">
          <label class="label">Name</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="name"
              placeholder="Group name."
            />
          </div>
        </div>
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
			name: '',
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
					name: this.name,
					order: parseInt(this.order, 10),
					weight: parseFloat(this.weight),
					app_id: this.application.id,
				});
			} finally {
				this.items = this.questionGroups.filter(qg => qg.application.id === this.application.id);
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
		this.items = this.questionGroups.filter(qg => qg.application.id === this.application.id).sort((a, b) => a.order - b.order);
		for (let i = 0; i < this.items.length; i++) {
			this.items[i].questions = this.items[i].questions.sort((a, b) => a.order - b.order);
		}
		this.loaded = true;
	},
};
</script>
