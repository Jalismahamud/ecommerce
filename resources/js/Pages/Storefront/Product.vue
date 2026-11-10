<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import StorefrontLayout from '../../Layouts/StorefrontLayout.vue';

const props = defineProps({
    shops: { type: Array, default: () => [] },
    shop: { type: Object, required: true },
    offer: { type: Object, required: true },
});

const quantity = ref(1);

function addToCart() {
    router.post('/cart', {
        shop_product_id: props.offer.id,
        quantity: quantity.value,
    }, { preserveScroll: true });
}

function price(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value));
}
</script>

<template>
    <Head :title="offer.name" />
    <StorefrontLayout :shop="shop" :shops="shops">
        <section class="container py-10">
            <nav class="mb-8 text-sm text-gray-500">
                <a href="/" class="hover:text-green-700">Home</a>
                <span class="mx-2">/</span>
                <span>{{ shop.name }}</span>
                <span class="mx-2">/</span>
                <span class="text-gray-800">{{ offer.name }}</span>
            </nav>

            <div class="grid gap-10 md:grid-cols-2">
                <div class="flex min-h-[360px] items-center justify-center rounded-lg border border-gray-200 bg-white p-8">
                    <img :src="offer.thumbnail" :alt="offer.name" class="max-h-[420px] w-full object-contain">
                </div>
                <div class="py-2">
                    <p class="mb-2 text-sm text-gray-500">{{ offer.category || 'Groceries' }} · {{ shop.name }}</p>
                    <h1 class="text-3xl font-semibold text-gray-900">{{ offer.name }}</h1>
                    <div class="mt-5 flex items-center gap-3">
                        <span class="text-2xl font-semibold text-gray-900">{{ price(offer.price) }}</span>
                        <span v-if="offer.compare_at_price" class="text-lg text-gray-400 line-through">{{ price(offer.compare_at_price) }}</span>
                    </div>
                    <p class="mt-5 whitespace-pre-line text-gray-600">{{ offer.description || 'Fresh quality groceries from your local shop.' }}</p>
                    <p class="mt-5 text-sm" :class="offer.stock > 0 ? 'text-green-700' : 'text-red-600'">
                        {{ offer.stock > 0 ? `${offer.stock} in stock` : 'Currently out of stock' }}
                    </p>
                    <div class="mt-6 flex max-w-sm gap-3">
                        <input v-model.number="quantity" type="number" min="1" :max="offer.stock" class="w-24 rounded-lg border border-gray-300 px-3 py-2" aria-label="Quantity">
                        <button class="flex-1 rounded-lg bg-green-600 px-5 py-3 font-medium text-white hover:bg-green-700 disabled:bg-gray-300" :disabled="offer.stock < 1" @click="addToCart">
                            Add to Cart
                        </button>
                    </div>
                    <div class="mt-8 border-t border-gray-200 pt-5 text-sm text-gray-600">
                        <p><strong class="text-gray-800">Shop:</strong> {{ shop.name }}</p>
                        <p class="mt-2"><strong class="text-gray-800">Availability:</strong> {{ offer.stock > 0 ? 'In stock' : 'Out of stock' }}</p>
                    </div>
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>
