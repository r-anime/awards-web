 <template>
    <div :id="slug" class="awardDisplay">
        <h2 class="categoryHeader title is-3 has-text-gold has-text-centered mt-100 pb-10 mb-20">{{category.name}}</h2>
        <award-winners v-if="nomPublicOrder[0] && nomJuryOrder[0]"
            :pub="nomPublicOrder[0]"
            :jury="nomJuryOrder[0]"
            :anilistData="anilistData"
        />
        <div class="categoryNominationContainer">
            <div class="categoryNominationHeader">
                <h3 class="categoryNominationTitle">
                    Nominees
                </h3>
            </div>
            <div class="categoryNominationCards">
                Public
                <div class="categoryNominationItem"
                    v-for="(nom, index) in nomPublicOrder"
                    :key = "index"
                >
                    {{nom.id}} {{data.anime[nom.id]}} {{data.characters[nom.id]}} {{data.themes[nom.id]}}
                    <category-item-image
                        :nominee="nom"
                        :anilistData="anilistData"
                    />
                </div>
            </div>
            <div class="categoryNominationCards">
                Jury
                <div class="categoryNominationItem"
                    v-for="(nom, index) in nomJuryOrder"
                    :key = "index"
                >
                    {{nom.id}} {{data.anime[nom.id]}} {{data.characters[nom.id]}} {{data.themes[nom.id]}}
                    <category-item-image
                        :nominee="nom"
                        :anilistData="data"
                    />
                </div>
            </div>
        </div>
        <div class="awardHonorableMentions" v-if="category.hms && category.hms.length > 0">
            <h4>Honorable Mentions</h4>
            <ul>
                <li v-for="(hm, index) in category.hms" :key="index">{{hm}}</li>
            </ul>
        </div>
    </div>
</template>
<script>
import util from '../util';
import AwardWinners from './ResultWinners';
import CategoryItemImage from './ItemImage';

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
		CategoryItemImage,
	},
	data () {
		return {
			nomPublicOrder: [].concat(this.category.nominees).sort((a, b) => b.public - a.public),
			nomJuryOrder: [].concat(this.category.nominees).sort((a, b) => a.jury - b.jury),
		};
	},
	computed: {
		slug () {
			return `category-${util.slugify(this.category.name)}`;
		},
	},
	mounted () {
		// console.log(this.anilistData);
	},
};
</script>
