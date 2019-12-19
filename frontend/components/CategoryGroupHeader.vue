<template>
    <div class="hero is-dark">
        <div class="hero-body">
            <div class="container">
                <h2 class="title is-size-1 is-size-2-mobile">{{title}}</h2>
                <p class="subtitle is-size-4 is-size-5-mobile">{{subtitle}}</p>
            </div>
        </div>
        <div class="hero-foot">
            <div class="container">
                <slot name="tab-bar"/>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	props: {
		title: String,
		subtitle: String,
	},
};
</script>

<style>
@import "../styles/old-public-voting-styles.scss";
</style>
