<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '../../Layouts/StorefrontLayout.vue';

defineProps({
    shops: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    itemCount: { type: Number, default: 0 },
});

function price(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value));
}

function updateQuantity(item, event) {
    router.patch(`/cart/${item.id}`, { quantity: Number(event.target.value) }, { preserveScroll: true });
}

function remove(item) {
    router.delete(`/cart/${item.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Shopping cart" />
    <StorefrontLayout :shops="shops">
        <section class="container py-10">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-gray-900">Your Shopping Cart</h1>
                <p class="mt-2 text-gray-500">{{ itemCount }} item(s) in your cart</p>
            </div>

            <div v-if="items.length" class="grid gap-8 lg:grid-cols-[1fr_320px]">
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <div v-for="item in items" :key="item.id" class="grid grid-cols-[80px_1fr_auto] items-center gap-4 border-b border-gray-200 p-4 last:border-b-0 sm:grid-cols-[100px_1fr_100px_100px_auto]">
                        <img :src="item.image" :alt="item.name" class="h-20 w-20 object-contain">
                        <div>
                            <Link :href="`/shops/${item.shop_slug}/${item.slug}`" class="font-semibold text-gray-900 hover:text-green-700">{{ item.name }}</Link>
                            <p class="mt-1 text-sm text-gray-500">{{ item.shop }}</p>
                            <button class="mt-2 text-sm text-red-600 hover:underline sm:hidden" @click="remove(item)">Remove</button>
                        </div>
                        <span class="hidden text-sm font-medium text-gray-800 sm:block">{{ price(item.price) }}</span>
                        <input
                            type="number"
                            min="1"
                            :max="item.stock"
                            :value="item.quantity"
                            class="w-20 rounded-md border border-gray-300 px-2 py-2"
                            :aria-label="`Quantity for ${item.name}`"
                            @change="updateQuantity(item, $event)"
                        >
                        <button class="hidden text-sm text-red-600 hover:underline sm:block" @click="remove(item)">Remove</button>
                    </div>
                </div>

                <aside class="h-fit rounded-lg border border-gray-200 p-5">
                    <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    <div class="mt-5 flex justify-between text-gray-600"><span>Subtotal</span><span>{{ price(total) }}</span></div>
                    <div class="mt-3 flex justify-between border-t border-gray-200 pt-4 text-lg font-semibold text-gray-900"><span>Total</span><span>{{ price(total) }}</span></div>
                    <p class="mt-3 text-xs text-gray-500">Delivery and payment are not included yet.</p>
                    <button class="mt-5 w-full cursor-not-allowed rounded-lg bg-gray-300 px-4 py-3 font-medium text-gray-600" disabled>Checkout coming soon</button>
                    <Link href="/" class="mt-4 block text-center text-sm text-green-700 hover:underline">Continue shopping</Link>
                </aside>
            </div>

            <div v-else class="rounded-lg border border-dashed border-gray-300 px-6 py-16 text-center">
                <i class="ti ti-shopping-cart mb-3 text-5xl text-gray-400"></i>
                <p class="text-lg font-medium text-gray-800">Your cart is empty</p>
                <p class="mt-2 text-gray-500">Discover fresh products from our shops.</p>
                <Link href="/" class="btn mt-5 inline-flex bg-green-600 text-white hover:bg-green-700">Start shopping</Link>
            </div>
        </section>
    </StorefrontLayout>
</template>
