<template>
	<router-view/>
</template>

<style lang="scss">
// Variables and customization are set in frontend/styles/main.scss, but we still
// need to import the actual styles once the variables are set.
@import url("https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Code+Pro:400");
// TODO: only import bulma components we actually use
@import "~bulma/sass/base/all";
@import "~bulma/sass/elements/all";
@import "~bulma/sass/form/all";
@import "~bulma/sass/components/all";
@import "~bulma/sass/grid/all";
@import "~bulma/sass/layout/all";

// Navbar menus on mobile shouldn't interfere with page content
@include touch {
	.navbar-menu {
		position: absolute;
		left: 0;
		right: 0;
	}
}

// Don't need to default to overflow-y: scroll
html {
	overflow-y: auto;
}

// Some other utility classes

// When you have a title inside a level, the title's margin is lost. Apply this
// class to the level to get it back.
.title-margin {
	margin-bottom: 1.5rem;
}
</style>
