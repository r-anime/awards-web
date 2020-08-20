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
@import "~bulma/sass/base/all";
@import "~bulma/sass/elements/all";
@import "~bulma/sass/form/all";
@import "~bulma/sass/components/all";
@import "~bulma/sass/grid/all";
@import "~bulma/sass/layout/all";
@import "../styles/results";

</style>
