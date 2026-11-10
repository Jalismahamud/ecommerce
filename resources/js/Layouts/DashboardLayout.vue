<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

defineProps({ title: { type: String, default: 'Dashboard' } });

const page = usePage();
const { mdAndUp } = useDisplay();
const drawer = ref(mdAndUp.value);
watch(mdAndUp, (isDesktop) => {
    drawer.value = isDesktop;
});
const isAdmin = computed(() => page.props.auth?.user?.roles?.includes('admin'));
const navigation = computed(() => isAdmin.value
    ? [
        { label: 'Overview', href: '/dashboard', icon: 'mdi-view-dashboard-outline' },
        { label: 'Shops', href: '/admin/shops', icon: 'mdi-store-outline' },
        { label: 'Shop owners', href: '/admin/shop-owners', icon: 'mdi-account-multiple-outline' },
        { label: 'Categories', href: '/admin/categories', icon: 'mdi-shape-outline' },
        { label: 'Catalog products', href: '/admin/products', icon: 'mdi-package-variant-closed' },
    ]
    : [
        { label: 'Overview', href: '/dashboard', icon: 'mdi-view-dashboard-outline' },
        { label: 'My shop products', href: '/shop/products', icon: 'mdi-package-variant-closed' },
    ]);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <v-app>
        <v-navigation-drawer
            v-model="drawer"
            :permanent="mdAndUp"
            :temporary="!mdAndUp"
            width="260"
            color="white"
        >
            <div class="pa-5">
                <a href="/" class="d-inline-block">
                    <img :src="'/images/fresh-food-logo.svg'" alt="Fresh Food" style="width: 165px">
                </a>
            </div>
            <v-divider />
            <div class="px-4 py-3 text-overline text-medium-emphasis">{{ isAdmin ? 'Administration' : 'Shop management' }}</div>
            <v-list nav density="comfortable">
                <v-list-item
                    v-for="item in navigation"
                    :key="item.href"
                    :prepend-icon="item.icon"
                    :title="item.label"
                    :active="page.url.startsWith(item.href)"
                    color="primary"
                    rounded="lg"
                    @click="router.visit(item.href)"
                />
            </v-list>
            <template #append>
                <div class="pa-4">
                    <v-divider class="mb-3" />
                    <div class="mb-3 text-body-2 font-weight-medium">{{ page.props.auth?.user?.name }}</div>
                    <v-btn variant="text" prepend-icon="mdi-logout" block @click="logout">Sign out</v-btn>
                </div>
            </template>
        </v-navigation-drawer>

        <v-main class="bg-grey-lighten-4">
            <v-app-bar flat color="white" class="border-b">
                <v-app-bar-nav-icon v-if="!mdAndUp" aria-label="Toggle navigation" @click="drawer = !drawer" />
                <v-app-bar-title class="text-h6 font-weight-semibold">{{ title }}</v-app-bar-title>
                <template #append>
                    <a href="/" class="mr-5 text-decoration-none text-medium-emphasis">View storefront</a>
                </template>
            </v-app-bar>
            <v-container fluid class="pa-6 pa-md-8">
                <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-5">{{ page.props.flash.success }}</v-alert>
                <v-alert v-if="page.props.flash?.error" type="error" variant="tonal" class="mb-5">{{ page.props.flash.error }}</v-alert>
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.v-list-item {
    margin-bottom: 4px;
}
</style>
