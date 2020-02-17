 <template>
    <div :id="slug" class="awardDisplay">
        <h2 class="categoryHeader">{{category.name}}</h2>
        <award-winners
            :pub="nomPublicOrder[0]"
            :jury="nomJuryOrder[0]"
        />
        <div class="categoryNominationContainer">
            <div class="categoryNominationHeader">
                <h3 class="categoryNominationTitle">
                    Nominees
                </h3>
                <div class="categorySwitchContainer">
                    <span class="categorySwitchLabel">
                        <span class="modalRankingJuryIcon"></span>
                    </span>
                    <label class="categorySwitch">
                        <input type="checkbox" checked="checked">
                        <span class="categorySwitchSlider"></span>
                    </label>
                    <span class="categorySwitchLabel">
                        <span class="modalRankingPublicIcon"></span>
                    </span>
                </div>
            </div>
            <div class="categoryNominationCards">
                <div class="categoryNominationItem"
                    :data-public="nom.public"
                    :data-jury="nom.jury"
                    v-for="(nom, index) in nomPublicOrder"
                    :key = "index"
                >
                    <category-item-image
                        :nominee="nom"
                    />
                </div>
            </div>
        </div>
        <div class="awardHonorableMentions" v-if="category.hms">
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

export default {
	props: ['category'],
	components: {
		AwardWinners,
	},
	computed: {
		slug () {
			return `category-${util.slugify(this.category.name)}`;
		},
		nomPublicOrder () {
			const noms = this.category.nominees;
			return noms.sort((a, b) => a.public - b.public);
		},
		nomJuryOrder () {
			const noms = this.category.nominees;
			return noms.sort((a, b) => a.jury - b.jury);
		},
	},
	mounted () {
		console.log(this.category);
	},
};
</script>
