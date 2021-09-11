const { all } = require("../routes/complain");

class ApplicationJuror {
	constructor (name, answers){
		this.name = name;
		this.answers = answers;
		this.prefs = [];

		this.wantedCats = 1;
		this.genreScore = -1;
		this.characterScore = -1;
		this.visualScore = -1;
		this.audioScore = -1;
		this.opedScore = -1;
		this.mainScore = -1;
		this.weightedScore = -1;

		this.calcWanted();
		this.calcScores();
		this.calcPrefs();
	}

	calcWanted(){
		const many = this.answers.filter(answer => answer.question.question.startsWith('How many categories') && answer.question.type === 'choice');
		if (many.length > 0) {
			this.wantedCats = parseInt(many[0].answer);
		} else {
			this.wantedCats = 1;
		}
	}

	calcScores(){
		// This is "kinda" hard coded and needs to be changed if application categories get changed
		const audioqs = this.answers.filter(answer => answer.question.question_group.name == 'Audio Production' && answer.question.type === 'essay');
		const opedqs = this.answers.filter(answer => answer.question.question_group.name == 'OP/ED' && answer.question.type === 'essay');
		const visualqs = this.answers.filter(answer => answer.question.question_group.name == 'Visual Production' && answer.question.type === 'essay');		
		const genreqs = this.answers.filter(answer => answer.question.question_group.name == 'Genre' && answer.question.type === 'essay');
		const chareqs = this.answers.filter(answer => answer.question.question_group.name == 'Character' && answer.question.type === 'essay');

		let weight = 0;
		const scoresreducer = (accumulator, b) => {
			return accumulator + (b.score?b.score:0);
		};
		const questionreducer = (accumulator, b) => {
			let asum = accumulator;
			let bsum = (b && b!=0 && b.scores.length > 0)?(b.scores.reduce(scoresreducer, 0)/b.scores.length):0;

			return asum + bsum;
		};
		this.genreScore = genreqs.reduce(questionreducer, 0) / (genreqs.length);
		if (!this.genreScore){this.genreScore = 0;} if (this.genreScore >= 1) {weight++;}
		this.characterScore = chareqs.reduce(questionreducer, 0) / (chareqs.length);
		if (!this.characterScore){this.characterScore = 0;} if (this.characterScore >= 1) {weight++;}
		this.audioScore = audioqs.reduce(questionreducer, 0) / (audioqs.length);
		if (!this.audioScore){this.audioScore = 0;} if (this.audioScore >= 1) {weight++;}
		this.visualScore = visualqs.reduce(questionreducer, 0) / (visualqs.length);
		if (!this.visualScore){this.visualScore = 0;} if (this.visualScore >= 1) {weight++;}
		this.opedScore = opedqs.reduce(questionreducer, 0) / (opedqs.length);
		if (!this.opedScore){this.opedScore = 0;} if (this.opedScore >= 1) {weight++;}

		this.mainScore = (this.genreScore + this.characterScore + this.visualScore + this.audioScore + this.opedScore) / 5;
		if (weight > 0){
			this.weightedScore = (this.genreScore + this.characterScore + this.visualScore + this.audioScore + this.opedScore) / weight;
		} else {
			this.weightedScore = 0;
		}
	}

	calcPrefs(){
		const prefqs = this.answers.filter(answer => answer.question.type == 'preference');

		// New Prefs
		if (prefqs.length == 1){
			this.prefs = JSON.parse(prefqs[0].answer);
		} 
		// Old Prefs (I am literally coding this part for testing with legacy data)
		else {
			let allprefs = [];
			prefqs.forEach(answer =>{
				let prefsobj = JSON.parse(answer.answer);
				let prefsort = [];
				for (let prefnum in prefsobj){
					allprefs.push([prefnum, parseInt(prefsobj[prefnum])]);
				}
			});
			allprefs.sort((a,b) => b[1] - a[1]);
			for (let catnum in allprefs){
				this.prefs.push(parseInt(allprefs[catnum]));
			}

		}
	}

	catPref(catid){
		return this.prefs.indexOf(catid);
	}
}

class Allocations {
	constructor (categories, apps) {
		// randomizing the categories so putting the same score for a genre and a production doesn't favour one over the other
		this.categories = this.shuffle(categories);
		this.apps = apps;
		this.jurors = [];
		this.allocatedJurors = [];

		this.apps.forEach(app => {
			if (app && app.user) {
				const juror = new ApplicationJuror(app.user.reddit, app.answers);
				this.jurors.push(juror);
			}
		});

		// Shuffle the Jurors so that ties are randomized in order
		this.jurors = this.shuffle(this.jurors);
		this.jurors.sort((a, b) => b.weightedScore - a.weightedScore);
	}

	// eslint-disable-next-line class-methods-use-this
	shuffle (array) {
		let currentIndex = array.length; let temporaryValue; let randomIndex;

		// While there remain elements to shuffle...
		while (0 !== currentIndex) {
			// Pick a remaining element...
			randomIndex = Math.floor(Math.random() * currentIndex);
			currentIndex -= 1;

			// And swap it with the current element.
			temporaryValue = array[currentIndex];
			array[currentIndex] = array[randomIndex];
			array[randomIndex] = temporaryValue;
		}

		return array;
	}



	priorityDraft(){
		this.categories.forEach(category => {
			// Skip Main Cats For Now
			if (category.awardsGroup === 'main'){
				return;
			}

			let shuffledJurors = this.shuffle(this.jurors);
			if (category.name.match(/Sound Design|OST|Voice Actor/gm)) {
				shuffledJurors = shuffledJurors.filter(juror => juror.audioScore >= 3);
				shuffledJurors.sort((a, b) => b.audioScore - a.audioScore);
			} else if (category.name.match(/OP|ED/gm)) {
				shuffledJurors = shuffledJurors.filter(juror => juror.opedScore >= 3);
				shuffledJurors.sort((a, b) => b.opedScore - a.opedScore);
			} else if (category.awardsGroup === 'production') {
				shuffledJurors = shuffledJurors.filter(juror => juror.opedScore >= 3);
				shuffledJurors.sort((a, b) => b.visualScore - a.visualScore);
			} else {
				shuffledJurors = shuffledJurors.filter(juror => juror.genreScore >= 3);
				shuffledJurors.sort((a, b) => b.genreScore - a.genreScore);
			}
			
		});
	}


}

module.exports = Allocations;
