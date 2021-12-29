<template>
	<router-view />
</template>

<style lang="scss">
// Loads utilities, variables, and basic bulma customizations with all our new
// colors
@use "../styles/utilities" as *;

// Import the Bulma styles themselves
// TODO: only import bulma components we actually use
@import "~bulma/sass/base/_all";
@import "~bulma/sass/elements/_all";
@import "~bulma/sass/form/_all";
@import "~bulma/sass/components/_all";
@import "~bulma/sass/grid/_all";
@import "~bulma/sass/layout/_all";
@import "~bulma/sass/helpers/_all";
@import "../styles/results";

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

.button.non-interactive {
    pointer-events: none;  /* Imitate the behavior of is-static class */
}

</style>
