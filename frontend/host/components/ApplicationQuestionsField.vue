<template>
    <div class="column is-12 is-6-desktop">
        <div class="notification">
			<h2 class="title is-3">{{questionGroup.name}}</h2>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Order</label>
                    <div class="control">
                        <input class="input" type="text" v-model="questionGroup.order" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Weight</label>
                    <div class="control">
                        <input class="input" type="text" v-model="questionGroup.weight" @input="emitUpdate">
                    </div>
                </div>
            </div>
			<div v-for="(question,index) in questionGroup.questions" :key="index">
				<button class="delete is-pulled-right" @click="deleteQuestion(index)"></button>
				<h2 class="title is-5">Question#{{index + 1}}</h2>
				<div v-if="question.type=='preference'">
					<label class="label">Category Type</label>
					<div class="control">
						<div class="select">
							<select v-model="question.question" @input="emitUpdate">
								<option value="" disabled>Select One</option>
								<option value="genre">Genre Awards</option>
								<option value="character">Character Awards</option>
								<option value="production">Production Awards</option>
								<option value="test">Test Category</option>
								<option value="main">Main Awards</option>
							</select>
						</div>
					</div>
				</div>
				<div v-else-if="question.type=='choice'">
					<label class="label">Choices</label>
					<div class="control">
						<textarea class="textarea" rows="5" v-model="question.question" @input="emitUpdate">
						</textarea>
					</div>
					<p class="help">Deliminate with new lines:<br>Question<br>Choice1<br>Choice2<br>etc...</p>
				</div>
				<div v-else>
					<label class="label">Question Text</label>
					<div class="control">
						<input class="textarea" type="text" rows="1" v-model="question.question" @input="emitUpdate">
					</div>
				</div>
				<div class="columns is-multiline">
					<div class="column is-narrow field">
						<label class="label">Order</label>
						<div class="control">
							<input class="input" type="text" v-model="question.order" @input="emitUpdate">
						</div>
					</div>
					<div class="column is-narrow field">
						<label class="label">Type</label>
						<div class="control">
							<select class="input" v-model="question.type" @input="emitUpdate">
								<option value="essay">Essay</option>
								<option value="choice">Multiple Choice (kinda implemented?)</option>
								<option value="preference">Preference</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<br/><br/>
			<div class="control">
				<div class="buttons">
					<button @click="updateGroup" class="button is-primary" :class="{'is-loading': updating}">Save</button>
					<button @click="addQuestion" class="button is-primary" >Add Question</button>
					<button @click="emitDelete" class="button is-danger" :class="{'is-loading': deleting}">Delete</button>
				</div>
			</div>
        </div>
    </div>
</template>

<script>
import {mapActions} from 'vuex';
export default {
	props: {
		questionGroup: Object,
		index: Number,
	},
	data () {
		return {
			deleting: false,
			updating: false,
		};
	},
	methods: {
		...mapActions(['deleteQuestionGroup', 'updateQuestionGroup']),
		emitUpdate () {
			this.$emit('toggle', this.questionGroup);
		},
		async emitDelete () {
			this.deleting = true;
			await this.deleteQuestionGroup(this.questionGroup.id);
			this.$emit('delete');
			this.deleting = false;
		},
		deleteQuestion (index) {
			this.questionGroup.questions.splice(index, 1);
			this.$emit('toggle', this.questionGroup);
		},
		addQuestion () {
			this.questionGroup.questions.push({
				question: '',
				type: 'essay',
				order: -1,
				group_id: this.questionGroup.id,
			});
			this.$emit('toggle', this.questionGroup);
		},
		async updateGroup () {
			if (!parseInt(this.questionGroup.order, 10) || !parseFloat(this.questionGroup.weight)) {
				// eslint-disable-next-line no-alert
				alert('Please enter numbers, not strings');
				return;
			}
			this.updating = true;
			await this.updateQuestionGroup(this.questionGroup);
			this.emitUpdate();
			this.updating = false;
		},
	},
};
</script>
