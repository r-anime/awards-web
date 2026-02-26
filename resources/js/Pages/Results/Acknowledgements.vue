<template>
    <div class="">
        <div class="container">
            <section class="section">
                <div class="container">
                    <h1 class="title is-2 has-text-gold has-text-left pb-20">Acknowledgements</h1>
                    <div v-for="acknowledgement in acknowledgements">
                        <h2 class="title is-5 has-text-gold has-text-centered pb-20">{{ acknowledgement.title }}</h2>
                        <div class="columns mb-6">
                            <div class="column has-text-centered has-text-periwinkle is-flex is-align-items-center">
                                {{  acknowledgement.english }}
                            </div>
                            <div v-if="acknowledgement.japanese" class="column has-text-centered has-text-periwinkle is-flex is-align-items-center">
                                {{ acknowledgement.japanese }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({ acknowledgements: Array });
</script>