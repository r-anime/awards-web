<template>
    <div>
        <div v-if="pub.jury === pub.public" class="categoryWinnerContainer" >
            <div class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury">
                <category-item-image
                    :nominee="pub"
                />
            </div>
        </div>
        <div v-else class="categoryWinnerContainer" >
            <div class="categoryWinnerItem categoryWinnerJury">
                <category-item-image
                    :nominee="jury"
                />
            </div>
            <div class="categoryWinnerItem categoryWinnerPublic">
                <category-item-image
                    :nominee="pub"
                />
            </div>
        </div>
        <div class="categorySubHeadContainer" v-if="pub.jury !== pub.public">
            <div class="categorySubHeadItem categorySubHeadJury">
                <div class="categorySubHeadItemIcon">
                    <img alt="laurels" src="img/assets/jury.png" />
                </div>
                <div class="categorySubHeadItemText">
                    <div class="categorySubHeadItemTextTitle">
                        <nominee-name
                        :nominee="jury"
                        ></nominee-name>
                    </div>
                    <div class="categorySubHeadItemTextSubTitle">
                        Jury Winner
                    </div>
                </div>
            </div>
            <div class="categorySubHeadItem categorySubHeadPublic">
                <div class="categorySubHeadItemIcon">
                    <img alt="laurels" src="img/assets/public.png" />
                </div>
                <div class="categorySubHeadItemText">
                    <div class="categorySubHeadItemTextTitle">
                        <nominee-name
                        :nominee="pub"
                        ></nominee-name>
                    </div>
                    <div class="categorySubHeadItemTextSubTitle">
                        Public Winner
                    </div>
                </div>
            </div>
        </div>
        <div class="categorySubHeadContainer" v-else>
            <div class="categorySubHeadItem categorySubHeadPublic categorySubHeadJury">
                <div class="categorySubHeadItemIcon">
                    <img alt="laurels" src="img/assets/pubjury.png" />
                </div>
                <div class="categorySubHeadItemText">
                    <div class="categorySubHeadItemTextTitle">
                        <nominee-name
                        :nominee="pub"
                        ></nominee-name>
                    </div>
                    <div class="categorySubHeadItemTextSubTitle">
                        Consensus Winner
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import CategoryItemImage from './ItemImage';
import NomineeName from './NomineeName';

export default {
	props: ['pub', 'jury'],
	components: {
		CategoryItemImage,
		NomineeName,
	},
	mounted () {
		console.log(this.jury, this.pub);
	},
};
</script>
