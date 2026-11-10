<script setup>
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from '../../Layouts/StorefrontLayout.vue';

defineProps({
    shops: { type: Array, default: () => [] },
    title: { type: String, required: true },
    updatedAt: { type: String, required: true },
    sections: { type: Array, required: true },
});
</script>

<template>
    <Head :title="title" />
    <StorefrontLayout :shops="shops">
        <article class="container max-w-4xl py-10 sm:py-14">
            <header class="mb-8 border-b border-gray-200 pb-6 sm:mb-10">
                <p class="mb-2 text-sm font-medium uppercase tracking-wide text-green-700">Fresh Food</p>
                <h1 class="text-3xl font-semibold text-gray-900 sm:text-4xl">{{ title }}</h1>
                <p class="mt-3 text-sm text-gray-500">Last updated: {{ updatedAt }}</p>
            </header>

            <div class="space-y-7 sm:space-y-8">
                <section v-for="section in sections" :key="section.heading">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900">{{ section.heading }}</h2>
                    <p class="leading-7 text-gray-600">{{ section.body }}</p>
                </section>
            </div>
        </article>
    </StorefrontLayout>
</template>
