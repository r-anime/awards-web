const { all } = require("../routes/complain");
const {yuuko} = require('../bot/index');

const JUROR_MIN = 7;
const FILL_MAX = 11;
const JUROR_MAX = 11;
const AOTY_MAX = 11;

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
		this.additionalCats = 0;
		this.willingToFill = true;
		this.immunity = 0;
		this.genreScore = -1;
		this.characterScore = -1;
		this.visualScore = -1;
		this.vaScore = -1;
		this.ostScore = -1;
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
			if (this.wantedCats > 3){
				this.additionalCats = this.wantedCats - 3;
				this.wantedCats = 3;
			}
		} else {
			this.wantedCats = 3;
		}
	}

	calcScores(){
		let totalavg = 0;
		let totalavgcount = 0;
		for (let answer of this.answers){
			for (let score of answer.score){
				if (score.subgrade === 'genre'){
					if (score.score > this.genreScore){
						this.genreScore = score.score;
					}
					totalavg += score.score;
					totalavgcount++;
				}
				else if (score.subgrade === 'char'){
					if (score.score > this.characterScore){
						this.characterScore = score.score;
					}
					totalavg += score.score;
					totalavgcount++;
				}
				else if (score.subgrade === 'visual'){
					if (score.score > this.visualScore){
						this.visualScore = score.score;
					}
				}
				else if (score.subgrade === 'va'){
					if (score.score > this.vaScore){
						this.vaScore = score.score;
					}
				}
				else if (score.subgrade === 'ost'){
					if (score.score > this.ostScore){
						this.ostScore = score.score;
					}
				}
				else if (score.subgrade === 'oped'){
					if (score.score > this.opedScore){
						this.opedScore = score.score;
					}
				}
				if (score.score > this.highestScore){
					this.highestScore = score.score;
				}
			}
		}
		this.mainScore = totalavg / totalavgcount;
		this.weightedScore = this.mainScore;
	}

	calcPrefs(){
		const prefqs = this.answers.filter(answer => answer.question.type == 'preference');

		// New Prefs
		if (prefqs.length == 1){
			this.prefs = JSON.parse(prefqs[0].answer);
		} 
		// Old Prefs (I am literally coding this part for testing with legacy data)
		else {
			let allprefs = new Array();
			prefqs.forEach(answer =>{
				let prefsobj = JSON.parse(answer.answer);
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

			if (category.name.match(/OST|Original Sound Track/gm)) {
				return this.ostScore;
			} else if (category.name.match(/Voice Actor|Voice Acting/gm)) {
				return this.vaScore;
			} else if (category.name.match(/OP|ED|Opening|Ending/gm)) {
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
R
	catPref(catid){
		try{
			let returnval = this.prefs.indexOf(catid);
			if (returnval == -1){
				return 883;
			} else {
				return returnval;
			}
		} catch {
			return 883;
		}
	}

	vaxinnate(){
		this.immunity++;
		try {
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'RNG was used',
					description: `To spite you specifically.`,
					color: 8302335,
				},
			});
		} catch (error) {
			response.error(error);
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

	catsNeedFill(){
		let catsBelowMin = this.categories.filter(cat => {
			let jurorsinCat = this.getJurorsInCat(cat.id);
			return jurorsinCat.length < FILL_MAX;
		});
		return catsBelowMin.length > 0;
	}

	catIsFull(catid, fill = false, override = FILL_MAX){
		let category = this.categories.find(cat => cat.id == catid);
		if (category === undefined){
			return true;
		}

		const catJurors = this.allocatedJurors.filter(juror => juror.catid == catid);
		if (category.name == "Anime of the Year"){
			return catJurors.length >= AOTY_MAX;
		} else {
			if (fill){
				return catJurors.length >= override;
			} else {
				return catJurors.length >= JUROR_MAX;
			}
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
			if (!fill){
				if (this.jurorIsFull(juror)){
					return false;
				}
			} else {
				const jurorAllocations = this.allocatedJurors.filter(aj => aj.name == juror.name);
				let catCount = juror.wantedCats;
				if (fill){
					catCount += juror.additionalCats;
				}
				if (jurorAllocations.length >= (catCount)){
					return false;
				}
			}
			if (juror.catPref(catid) === 883){
				return false;
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
	
	getUnallocatedJurors(){
		return this.jurors.filter((juror) => {
			for (let allocatedjuror of this.allocatedJurors){
				if (juror.name == allocatedjuror.name){
					return false;
				}
			}
			return true;
		});
	}

	vaxiDraft(low, aotylow, fill=false){
		// Get the list of categories
		let eligibleCats = this.categories;
		console.log("Main Draft", low, aotylow, fill);

		// Loop through the categories
		for (let draft = 0; draft < eligibleCats.length; draft++){
			for (let cat of eligibleCats){
				// console.log(cat.name);
				// Get the list of eligible jurors
				let catJurors = [];
				// If we are in the AotY category, only get the jurors that qualify for it
				if (cat.name == 'Anime of the Year'){
					catJurors = this.getEligibleJurors(cat.id, aotylow, fill);
				} else {
					catJurors = this.getEligibleJurors(cat.id, low, fill);
				}
				// Get catJurors of increasing breadth of preference as the draft increases
				catJurors = catJurors.filter(juror => juror.catPref(cat.id) <= draft);

				// By sorting by immunity -> score -> preference, we actually end up with a list where the reverse is prioritized
				// Thus we end up with a juror list that is sorted by preference, then score as a tiebreaker, then immunity as a tiebreaker
				catJurors.sort((a, b) => b.immunity - a.immunity);
				catJurors.sort((a, b) => Math.round(b.qualifyingScore(cat.id)) - Math.round(a.qualifyingScore(cat.id)));
				catJurors.sort((a, b) => a.catPref(cat.id) - b.catPref(cat.id));
				
				let lastAllocatedJuror = null; // this is important for determining category immunity
				// Loop through the jurors while we have space and jurors
				while (!this.catIsFull(cat.id) && catJurors.length > 0){
					// console.log(catJurors.length);
					// Get the first juror in the list and attempt to allocate them
					let juror = catJurors.shift();
					if (this.jurorIsFull(juror)){
						continue;
					}
					this.allocateJuror(juror, cat.id);
				}

				// If we allocated a juror, we need distribute immunity points to everyone who had the same or somehow higher preference
				if (lastAllocatedJuror !== null){
					let unluckyJurors = catJurors.filter(juror => (juror.catPref(cat.id) <= lastAllocatedJuror.catPref(cat.id) && !this.jurorIsFull(juror)));
					for (let juror of unluckyJurors){
						// This literally just increments immunity, but I liked the pun
						juror.vaxinnate();
					}
				}
			}
		}

	}

	pandaDraft(low, aoty=2.8, fill=false){
		// Set up helper variables we need for this draft
		let succesfulAllocations = 0;
		let loopedCats = 0;

		// We will randomize the order of the categories so that we don't always start with the same category
		let eligibleCats = shuffle(this.categories);
		console.log("Fill Draft", low, aoty, fill);

		do { // Do while ensures this happens at least once
			let lowestCount = -1;
			let _this = this;

			succesfulAllocations = 0;
			loopedCats = 0;

			// Shuffle the categories that aren't full then sort them by the number of jurors they already contain
			// As explained in vaxi's algorithm, this actually uses the first sort as a tiebreaker
			// So the categories will be sorted by the number of jurors they already contain, then tiebroken by the number of available jurors
			eligibleCats = eligibleCats.filter(cat => !this.catIsFull(cat.id, true));
			eligibleCats.sort((a, b) => {
				let acount = 0;
				let bcount = 0;

				if (a.name == 'Anime of the Year'){
					acount = this.getEligibleJurors(a.id, aoty, fill);
				} else {
					acount = this.getEligibleJurors(a.id, low, fill);
				}

				if (a.name == 'Anime of the Year'){
					bcount = this.getEligibleJurors(b.id, aoty, fill); 
				} else {
					bcount = this.getEligibleJurors(b.id, low, fill);
				}

				return acount - bcount;
			});
			eligibleCats.sort((a, b) => _this.getJurorsInCat(a).length - _this.getJurorsInCat(b).length);

			// Loop through the categories
			for (let cat of eligibleCats){
				loopedCats++;

				// Get the list of eligible jurors
				let eligibleJurors;
				if (cat.name == 'Anime of the Year'){
					eligibleJurors = this.getEligibleJurors(cat.id, aoty, fill);
				} else {
					eligibleJurors = this.getEligibleJurors(cat.id, low, fill);
				}
				// We don't care about categories no one likes
				if (eligibleJurors.length == 0){
					continue;
				}
				// console.log(`Eligible jurors for ${cat.name}: ${eligibleJurors.length}`);

				// The first category we see is the category with the lowest count. We will use this count to even out categories if needed.
				// If we encounter another category with a higher count than the current lowest, we will break out of the loop
				// Categories with the same count will still have a juror allocated to it this cycle
				// This ensures we prioritize even distribution of jurors across categories
				let categoryJurors = this.getJurorsInCat(cat.id);
				if (lowestCount < 0){
					lowestCount = categoryJurors.length;
				} else {
					if (categoryJurors.length > lowestCount){
						break;
					}
				}
				
				// By sorting by shuffle -> score -> preference, we actually end up with a list where the reverse is prioritized
				// Thus we end up with a juror list that is sorted by preference, then score as a tiebreaker, then shuffle as a tiebreaker
				eligibleJurors = shuffle(eligibleJurors);
				eligibleJurors.sort((a, b) => Math.floor(b.qualifyingScore(cat.id)) - Math.floor(a.qualifyingScore(cat.id)));
				eligibleJurors.sort((a, b) => a.catPref(cat.id) - b.catPref(cat.id));

				// Choose the "best" juror for the category and allocate them
				if (low <= 1.0){
					if (eligibleJurors.length > 0 && !this.catIsFull(cat.id, true, 11)){
						this.allocateJuror(eligibleJurors[0], cat.id);
						succesfulAllocations++;
					}
				} else {
					if (eligibleJurors.length > 0 && !this.catIsFull(cat.id, true)){
						this.allocateJuror(eligibleJurors[0], cat.id);
						succesfulAllocations++;
					}
				}
			}
			// console.log(`${succesfulAllocations}/${loopedCats}/${eligibleCats.length}`);
		}
		// We will keep allocating until the algorithm has looped through all the categories and has failed to allocate any additional jurors,
		// Whether it be because the categories are full or because there are no more eligible jurors for any categories.
		while (succesfulAllocations > 0 || loopedCats < eligibleCats.length);
	}
}

module.exports = Allocations;
