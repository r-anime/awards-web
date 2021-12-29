<template>
	<router-view/>
</template>

<style lang="scss">
// Loads utilities, variables, and basic bulma customizations with all our new
// colors
@use "../styles/utilities" as *;

// Further customization before we import elements
@import "../styles/results-bulma";

// Finally, import the elements themselves
// TODO: only import bulma components we actually use
@import "~bulma/sass/base/_all";
@import "~bulma/sass/elements/_all";
@import "~bulma/sass/form/_all";
@import "~bulma/sass/components/_all";
@import "~bulma/sass/grid/_all";
@import "~bulma/sass/layout/_all";
@import "~bulma/sass/helpers/_all";
@import "../styles/results";

</style>
