const { all } = require("../routes/complain");

const HIGHPRIO_THRESHOLD = 3.5;
const MAIN_THRESHOLD = 2.5;
const NORMAL_THRESHOLD = 3;
const LOWPRIO_THRESHOLD = 2;
const SCRAPING_THRESHOLD = 1;
const JUROR_MIN = 5;
const JUROR_MAX = 13;
const AOTY_MAX = 19;

function shuffle (array) {
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

class ApplicationJuror {
	constructor (name, answers){
		this.name = name;
		this.answers = answers;
		this.prefs = new Array();

		this.wantedCats = 3;
		this.willingToFill = true;
		this.genreScore = -1;
		this.characterScore = -1;
		this.visualScore = -1;
		this.audioScore = -1;
		this.opedScore = -1;
		this.mainScore = -1;
		this.weightedScore = -1;
		this.highestScore = 0;

		this.calcWanted();
		this.calcScores();
		this.calcPrefs();
	}

	calcWanted(){
		const many = this.answers.filter(answer => answer.question.question.startsWith('How many categories') && answer.question.type === 'choice');
		if (many.length > 0) {
			this.wantedCats = parseInt(many[0].answer);
		} else {
			this.wantedCats = 3;
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
		if (!this.opedScore){this.opedScore = 0;} // if (this.opedScore >= 1) {weight++;}

		this.mainScore = (this.genreScore + this.characterScore + this.visualScore + this.audioScore) / 4;
		if (weight > 0){
			this.weightedScore = (this.genreScore + this.characterScore + this.visualScore + this.audioScore) / weight;
		} else {
			this.weightedScore = 0;
		}

		this.highestScore = Math.max(this.visualScore, this.audioScore, this.characterScore, this.genreScore, this.opedScore, 0);
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
					if (parseInt(prefsobj[prefnum]) > 1){
						allprefs.push([prefnum, parseInt(prefsobj[prefnum])]);
					}
				}
			});
			allprefs = shuffle(allprefs);
			allprefs.sort((a,b) => b[1] - a[1]);
			for (let catnum in allprefs){
				this.prefs.push(parseInt(allprefs[catnum]));
			}
		}
	}

	qualifiesFor(cats, catid, threshold){
		return this.qualifyingScore(cats, catid) >= threshold;
	}

	qualifyingScore(cats, catid){
		try {
			let category = cats.find(cat => cat.id == catid);

			if (category === undefined){
				// console.log(`cant find ${catid}`)
				return 0;
			}

			if (category.name.match(/Sound Design|OST|Voice Actor/gm)) {
				return this.audioScore;
			} else if (category.name.match(/OP|ED/gm)) {
				return this.opedScore;
			} else if (category.awardsGroup === 'production') {
				return this.visualScore;
			} else if (category.awardsGroup === 'main') {
				return this.mainScore;
			} else if (category.awardsGroup === 'character'){
				return this.characterScore;
			} else if (category.awardsGroup === 'genre'){
				return this.genreScore;
			}
			return 0;
		} catch (e) {
			return 0;
		}
	}

	// all preferences for which we have a qualifying score (passes this threshold)
	qualifyingPrefs(cats, threshold){
		try {
			return this.prefs.filter(x => this.qualifiesFor(cats, x, threshold))
		} catch {
			return [];
		}
	}

	catPref(catid){
		try{
			return this.prefs.indexOf(catid);
		} catch {
			return -1;
		}
	}
}

class AllocatedJuror{
	constructor (name, score, pref, catid){
		this.name = name;
		this.link = `https://www.reddit.com/user/${name}`;
		this.score = score;
		this.pref = pref;
		this.catid = catid;
	}
}

class Allocations {
	constructor (categories, apps) {
		// randomizing the categories so putting the same score for a genre and a production doesn't favour one over the other
		this.categories = shuffle(categories);
		this.apps = apps;
		this.jurors = [];
		this.allocatedJurors = [];

		this.apps.forEach(app => {
			if (app && app.user) {
				const juror = new ApplicationJuror(app.user.reddit, app.answers);
				this.jurors.push(juror);
			}
		});
	}

	catIsFull(catid){
		let category = this.categories.find(cat => cat.id == catid);
		if (category === undefined){
			return true;
		}

		const catJurors = this.allocatedJurors.filter(juror => juror.catid == catid);
		if (category.name == "Anime of the Year"){
			return catJurors.length >= AOTY_MAX;
		} else {
			return catJurors.length >= JUROR_MAX;
		}
	}

	catExists(catid){
		let category = this.categories.find(cat => cat.id == catid);

		return category !== undefined;
	}

	jurorIsFull(juror){
		const jurorAllocations = this.allocatedJurors.filter(aj => aj.name == juror.name);
		return (jurorAllocations.length >= juror.wantedCats);
	}

	jurorInCat(juror, catid){
		let allocatedJuror = this.allocatedJurors.find(aj => aj.name == juror.name && aj.catid == catid);

		return allocatedJuror !== undefined;
	}

	getEligibleJurors(catid, threshold, fill=false){
		let category = this.categories.find(cat => cat.id == catid);
		if (category === undefined){
			return [];
		}

		let jurors = this.jurors.filter(juror => {
			if (juror.catPref(catid) === -1){
				return false;
			}
			if (fill){
				if (this.jurorIsFull(juror)){
					return false;
				}
			} else {
				const jurorAllocations = this.allocatedJurors.filter(aj => aj.name == juror.name);
				if (jurorAllocations.length >= (juror.wantedCats+1)){
					return false;
				}
			}
			if (this.jurorInCat(juror, catid)){
				return false;
			}
			return juror.qualifiesFor(this.categories, catid, threshold);
		});

		return jurors;
	}

	getJurorsInCat(catid){
		let category = this.categories.find(cat => cat.id == catid);
		if (category === undefined){
			return [];
		}
		
		let allocatedJurors = this.allocatedJurors.filter(aj => aj.catid == catid);
		return allocatedJurors;
	}

	allocateJuror(juror, catid){
		let allocatedJuror = new AllocatedJuror(juror.name, juror.qualifyingScore(this.categories, catid), juror.catPref(catid), catid);
		this.allocatedJurors.push(allocatedJuror);
	}

	priorityDraft(){
		let eligibleJurors = shuffle(this.jurors);
		eligibleJurors = eligibleJurors.filter(juror => juror.highestScore >= HIGHPRIO_THRESHOLD);
		eligibleJurors.sort((a, b) => b.highestScore - a.highestScore);

		for (let juror of eligibleJurors){
			const nonMainCats = this.categories.filter(cat => cat.name != 'Anime of the Year');
			let eligiblePrefs = juror.qualifyingPrefs(nonMainCats, juror.highestScore);
			if (eligiblePrefs.length > 0 && !this.catIsFull(eligiblePrefs[0])){
				this.allocateJuror(juror, eligiblePrefs[0]);
			} else {
				let eligiblePrefs = juror.qualifyingPrefs(nonMainCats, HIGHPRIO_THRESHOLD);
				for (let pref of eligiblePrefs){
					if (!this.catExists(pref)){
						continue;
					}
					if (!this.catIsFull(pref) && !this.jurorInCat(juror, pref)){
						this.allocateJuror(juror, pref);
						break;
					}
				}
			}
		}
	}
	
	mainCatDraft(){
		let eligibleJurors = shuffle(this.jurors);
		eligibleJurors = eligibleJurors.filter(juror => juror.mainScore >= MAIN_THRESHOLD);
		// We do not order by score for AotY Draft, only thresholds
		// eligibleJurors = eligibleJurors.sort((a, b) => b.mainScore - a.mainScore);

		for (let juror of eligibleJurors){
			if (this.jurorIsFull(juror)){
				continue;
			}
			const mainCats = this.categories.filter(cat => cat.awardsGroup == 'Anime of the Year');
			let eligiblePrefs = juror.qualifyingPrefs(mainCats, MAIN_THRESHOLD);
			if (eligiblePrefs.length > 0 && !this.catIsFull(eligiblePrefs[0])){
				this.allocateJuror(juror, eligiblePrefs[0]);
			}
			/* No more priority for missing AotY, you snooze you lose
			else {
				let eligiblePrefs = juror.qualifyingPrefs(this.categories, MAIN_THRESHOLD);
				for (let pref of eligiblePrefs){
					if (!this.catExists(pref)){
						continue;
					}
					if (!this.catIsFull(pref) && !this.jurorInCat(juror, pref)){
						let newJuror = new AllocatedJuror(juror.name, juror.qualifyingScore(this.categories, pref), juror.catPref(pref), pref);
						this.allocatedJurors.push(newJuror);
						break;
					}
				}
			}*/
		}
	}

	draft(low, fill=false){		
		let succesfulAllocations = 0;
		let loopedCats = 0;
		let eligibleCats = shuffle(this.categories);

		do {
			let lowestCount = -1;
			let _this = this;

			succesfulAllocations = 0;
			loopedCats = 0;
			
			eligibleCats = shuffle(this.categories);
			eligibleCats = eligibleCats.filter(cat => !this.catIsFull(cat.id));
			eligibleCats.sort((a, b) => _this.getJurorsInCat(a).length - _this.getJurorsInCat(b).length);

			// console.log(eligibleCats);

			for (let cat of eligibleCats){
				loopedCats++;

				let eligibleJurors;
				if (cat.name == 'Anime of the Year'){
					eligibleJurors = this.getEligibleJurors(cat.id, 3, fill);
				} else {
					eligibleJurors = this.getEligibleJurors(cat.id, low, fill);
				}
				eligibleJurors = shuffle(eligibleJurors);
				console.log(`Eligible jurors for ${cat.name}: ${eligibleJurors.length}`);

				let categoryJurors = this.getJurorsInCat(cat.id);
				if (lowestCount < 0){
					lowestCount = categoryJurors.length;
				} else {
					if (categoryJurors.length > lowestCount){
						break;
					}
				}
				eligibleJurors.sort((a, b) => Math.floor(b.qualifyingScore(cat.id)) - Math.floor(a.qualifyingScore(cat.id)));
				eligibleJurors.sort((a, b) => a.catPref(cat.id) - b.catPref(cat.id));

				if (eligibleJurors.length > 0 && !this.catIsFull(cat.id)){
					this.allocateJuror(eligibleJurors[0], cat.id);
					succesfulAllocations++;
				}
			}
			console.log(`${succesfulAllocations}/${loopedCats}/${eligibleCats.length}`);
		}
		while (succesfulAllocations > 0 || loopedCats < eligibleCats.length);
	}
}

module.exports = Allocations;
