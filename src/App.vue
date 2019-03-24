<template>
	<router-view/>
</template>

<style lang="scss">
// Variables and customization are set in src/styles/main.scss, but we still
// need to import the actual styles once the variables are set.
@import url("https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Code+Pro:400");
// TODO: only import bulma components we actually use
@import "~bulma/sass/base/all";
@import "~bulma/sass/layout/all";
@import "~bulma/sass/elements/all";
@import "~bulma/sass/components/all";
</style>
