<script setup>
// import Layout from './Layout'
import { Head } from '@inertiajs/vue3'

defineProps({ username: String })
</script>

<template>
    <div>
        <Head title="Inertia test page" />
        <h1>Inertia test page</h1>
        <p>Hello {{ username ?? 'Guest' }}, welcome to Inertia Test!</p>
    </div>
</template>