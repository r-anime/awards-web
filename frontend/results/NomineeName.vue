<template>
    <span v-if="nominee.altname != ''">
        {{nominee.altname}}
    </span>
    <span v-else>
        {{nominee.id}}
    </span>
</template>
<script>
export default {
	props: ['nominee'],
};
</script>
