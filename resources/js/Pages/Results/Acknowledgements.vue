<template>
    <div class="has-background-dark">
        <div class="container">
            <section class="section">
                <div class="container">
                    <h1 class="title is-2 has-text-gold has-text-left pb-20">Acknowledgements</h1>
                    <div v-for="acknowledgement in acknowledgements">
                        <h2 class="title is-5 has-text-gold has-text-centered pb-20">{{ acknowledgement.title }}</h2>
                        <div class="columns mb-6">
                            <div class="column has-text-centered has-text-periwinkle is-flex is-align-items-center"
                                :style="'white-space: pre-wrap;'" v-text="acknowledgement.content.english"
                            />
                            <div v-if="acknowledgement.content.japanese" class="column has-text-centered has-text-periwinkle is-flex is-align-items-center"
                                :style="'white-space: pre-wrap;'" v-text="acknowledgement.content.japanese"
                            />
                        </div>
                        <div v-if="acknowledgement.content.extra" class="columns mb-6">
                            <div class="column has-text-centered has-text-periwinkle is-flex-grow-1 is-align-items-center"
                                :style="'white-space: pre-wrap;'" v-html="markdownit(acknowledgement.content.extra)"
                            />
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { markdownit } from '../../utils';

const props = defineProps({ acknowledgements: Array });
props.acknowledgements.forEach(ack => ack.content = JSON.parse(ack.content));
</script>