<template>
    <div :id="slug" class="awardDisplay mb-100">
		<div class="mb-6" @click="emitCatModal">
			<div class="is-pulled-left">
				<h2 class="categoryHeader title is-2 has-text-light pl-5" >
					{{category.name}}
				</h2>
			</div>
			<br class="is-hidden-desktop" />
			<button class="button is-platinum is-pulled-right mr-5 mt-3">
				<span class="icon mr-4"><fa-icon icon="info-circle" /></span>
				Read Category Info
			</button>
		</div>
		<div class="is-clearfix my-3"></div>
        <award-winners v-if="nomPublicOrder[0] &&
            nomJuryOrder[0]"
            :pub="nomPublicOrder[0]"
            :jury="nomJuryOrder[0]"
            :anilistData="anilistData"
            :data="data"
            :category="category"
            @nomModal="emitNomModal"
        />
		<div class="">
			<div class="">
				<h5 class="is-pulled-left has-text-light is-size-4 ml-2">Nominees</h5>
				<div class="juryToggle is-pulled-right mr-2 is-inline-flex">
					<img class="image mr-2" :src="juryIcon" width="42" height="34"/>
					<label class="switch">
						<input v-model="focus" type="checkbox">
						<span class="slider round">
						</span>
					</label>
					<img class="image" :src="publicIcon" width="42" height="34"/>
				</div>
			</div>
			<div class="is-clearfix my-3"></div>
			<div>
					<transition-group name="nominees" tag="div" class="categoryNominationCards columns is-gapless is-marginless is-mobile is-multiline">
						<div class="categoryRankCard column is-half-mobile" v-for="(nom, index) in nomCurrentOrder"
						:key="nom.id" @click="emitNomModal(nom)">
							<div class="categoryNominationItem" >
								<div class="categoryItemImage" :title="nom.id" :style="itemImage(nom)">
    						</div>
								<div class="nomineeTitle has-text-light is-size-6">
									<span>
                  {{nomineeName(nom)}}
                  </span>
								</div>
							</div>
							<div v-if="focus" class="categoryRank has-text-gold">{{nomPublicRankings[index]}}</div>
							<div v-else class="categoryRank has-text-gold">{{nom.jury}}</div>
						</div>
					</transition-group>
					<small class="is-pulled-right has-text-light small mr-3">Click each nominee to read a detailed write-up.</small>
					<div class="is-clearfix"></div>
			</div>
		</div>
        <div class="awardHonorableMentions has-text-light has-text-centered my-6" v-if="category.hms && category.hms.length > 0">
            <h5 class="is-pulled-left has-text-light is-size-4 ml-2">Honorable Mentions</h5>
			<div class="is-clearfix mb-4"></div>
            <div class="columns is-multiline mx-2 is-mobile">
				<div class="column is-half-mobile is-one-quarter-tablet is-clickable" v-for="(hm, index) in category.hms" :key="index" @click="emitHMModal(hm)">
					<div class="awardHonorableMention p-4">{{hm.name}}</div>
                </div>
            </div>
			<small class="is-pulled-right has-text-light small mr-3">Click each HM to read a detailed write-up (if available).</small>
			<div class="is-clearfix"></div>
        </div>
    </div>
</template>

<script>
import {mapActions} from 'vuex';
import util from '../../util';
import AwardWinners from './ResultWinners';
import marked from 'marked';
import juryIcon from '../../../img/jury.png';
import publicIcon from '../../../img/public.png';

export default {
	props: [
		'category',
		'data',
		'showData',
		'charData',
		'anilistData',
	],
	components: {
		AwardWinners,
	},
	data () {
		return {
			focus: false,
			juryIcon,
			publicIcon,
		};
	},
	computed: {
		slug () {
			return `category-${util.slugify(this.category.name)}`;
		},
		nomPublicOrder () {
			return [].concat(this.category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
		},
		nomJuryOrder () {
			return [].concat(this.category.nominees).filter(nom => nom.jury !== -1).sort((a, b) => a.jury - b.jury);
		},
		nomPublicRankings () {
			const po = [].concat(this.category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
			const ranking = [];
			for (let i = 0; i < po.length; i++) {
				ranking.push(i + 1);
				if (i < po.length - 1 && typeof po[i].public !== 'undefined' && po[i + 1].public && po[i].public === po[i + 1].public) {
					ranking.push(i + 1);
					i++;
				}
			}
			return ranking;
		},
		nomCurrentOrder () {
			if (this.focus) {
				return this.nomPublicOrder;
			}
			return this.nomJuryOrder;
		},
	},
	methods: {
		...mapActions(['sendAnalytics']),
		publicOrder () {
			this.focus = 'public';
		},
		juryOrder () {
			this.focus = 'jury';
		},
		emitNomModal (nom) {
			const ranking = this.getPRgivenShow(nom);
			const _catname = this.category.name;
			const _nomid = nom.id;
			const _juryrank = nom.jury;

			this.$plausible.trackEvent('view-nomination', {props: {anilistid: _nomid, category: _catname, juryrank: _juryrank, publicrank: ranking}});
			this.$emit('nomModal', nom, ranking, this.category);
		},
		emitHMModal (hm) {
			const _hmname = hm.name;

			this.$plausible.trackEvent('view-hm', {props: {name: _hmname}});
			this.$emit('hmModal', hm, this.category);
		},
		emitCatModal () {
			const _catname = this.category.name;
			const _cattype = this.category.entryType;

			this.$plausible.trackEvent('view-category', {props: {name: _catname, type: _cattype}});
			this.$emit('hmModal', null, this.category);
		},
		markdownit (writeup) {
			return marked(writeup);
		},
		getPRgivenShow (nominee) {
			const index = this.nomPublicOrder.findIndex(nom => nom.id === nominee.id);
			return this.nomPublicRankings[index];
		},
		beforeLeave (el) {
			const {marginLeft, marginTop, width, height} = window.getComputedStyle(el);

			el.style.left = `${el.offsetLeft - parseFloat(marginLeft, 10)}px`;
			el.style.top = `${el.offsetTop - parseFloat(marginTop, 10)}px`;
			el.style.width = width;
			el.style.height = height;
		},
		itemImage (nom) {
			if (this.anilistData) {
				if (nom.altimg !== '') {
					return `background-image: url(${nom.altimg})`;
				}
				const found = this.anilistData.find(el => el.id === nom.id);
				if (found && found.image) {
					if (found.image.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.image.large) {
						return `background-image: url(${found.image.large})`;
					}
				}
				if (found && found.coverImage) {
					if (found.coverImage.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.coverImage.large) {
						return `background-image: url(${found.coverImage.large})`;
					}
				}
			}
			return 'background-image: none';
		},
		nomineeName (nom) {
			if (nom.altname !== '') {
				return nom.altname;
			}
			if (this.category.entryType === 'themes') {
				return this.data.themes[nom.id].split(/ OP| ED/)[0];
			} else if (this.category.entryType === 'vas') {
				return `${this.data.characters[nom.id].name}`;
			} else if (this.category.entryType === 'characters') {
				return `${this.data.characters[nom.id].name}`;
			}

			const found = this.anilistData.find(el => el.id === nom.id);

			if (found && found.title) {
				return found.title.romaji || found.title.english;
			}
			if (found && found.name) {
				return found.name.full;
			}
			return 'ERROR';
		},
	},
};
</script>
