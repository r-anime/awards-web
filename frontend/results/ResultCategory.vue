 <template>
    <div :id="slug" class="awardDisplay">
        <h2 class="categoryHeader title is-3 has-text-light">{{category.name}}</h2>
        <award-winners v-if="nomPublicOrder[0] && nomJuryOrder[0]"
            :pub="nomPublicOrder[0]"
            :jury="nomJuryOrder[0]"
        />
        <div class="categoryNominationContainer">
            <div class="categoryNominationHeader">
                <h3 class="categoryNominationTitle">
                    Nominees
                </h3>
            </div>
            <div class="categoryNominationCards">
                <div class="categoryNominationItem"
                    v-for="(nom, index) in nomPublicOrder"
                    :key = "index"
                >
                    {{nom.id}}
                    <category-item-image
                        :nominee="nom"
                    />
                </div>
            </div>
            <div class="categoryNominationCards">
                <div class="categoryNominationItem"
                    v-for="(nom, index) in nomJuryOrder"
                    :key = "index"
                >
                    {{nom.id}}
                    <category-item-image
                        :nominee="nom"
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
	props: ['category'],
	components: {
		AwardWinners,
		CategoryItemImage,
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
		// console.log(this.category);
	},
};
</script>
