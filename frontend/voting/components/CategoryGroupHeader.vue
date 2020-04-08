<template>
    <div class="hero is-dark">
        <div class="hero-body">
            <div class="container">
                <h2 class="title is-size-1 is-size-2-mobile">{{title}}</h2>
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
	},
};
</script>
