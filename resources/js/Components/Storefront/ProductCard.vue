<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    offer: { type: Object, required: true },
    shop: { type: Object, required: true },
});

function addToCart() {
    router.post('/cart', {
        shop_product_id: props.offer.id,
        quantity: 1,
    }, { preserveScroll: true });
}

function price(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value));
}
</script>

<template>
    <article class="relative break-words rounded-lg border border-gray-300 bg-white card-product">
        <div class="flex-auto p-4">
            <div class="relative flex justify-center text-center">
                <div v-if="Number(offer.compare_at_price) > Number(offer.price)" class="absolute left-0 top-0">
                    <span class="inline-block rounded bg-green-600 p-1 text-center text-sm font-semibold leading-none text-white">
                        {{ Math.round((1 - Number(offer.price) / Number(offer.compare_at_price)) * 100) }}%
                    </span>
                </div>
                <Link :href="`/shops/${shop.slug}/${offer.slug}`">
                    <img :src="offer.image" :alt="offer.name" class="h-auto w-full">
                </Link>

                <div class="invisible absolute bottom-[15%] w-full opacity-0 card-product-action">
                    <Link
                        :href="`/shops/${shop.slug}/${offer.slug}`"
                        class="inline-flex h-[34px] w-[34px] items-center justify-center rounded-lg bg-white leading-[34px] shadow hover:bg-green-600 hover:text-white"
                        :aria-label="`View ${offer.name} details`"
                    >
                        <i class="ti ti-eye"></i>
                    </Link>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <Link
                    v-if="offer.category"
                    :href="`/?shop=${shop.slug}&category=${encodeURIComponent(offer.category_slug || '')}`"
                    class="text-decoration-none text-gray-500"
                >
                    <small>{{ offer.category }}</small>
                </Link>
                <small v-else class="text-gray-500">Groceries</small>

                <div class="flex flex-col gap-2">
                    <h3 class="truncate text-base">
                        <Link :href="`/shops/${shop.slug}/${offer.slug}`" class="text-gray-900 hover:text-green-700">{{ offer.name }}</Link>
                    </h3>
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <span class="font-semibold text-gray-900">{{ price(offer.price) }}</span>
                            <span v-if="Number(offer.compare_at_price) > Number(offer.price)" class="ml-1 text-sm text-gray-500 line-through">{{ price(offer.compare_at_price) }}</span>
                        </div>
                        <button
                            type="button"
                            class="btn inline-flex items-center gap-x-1 border-green-600 bg-green-600 px-3 py-2 text-sm text-white hover:border-green-700 hover:bg-green-700 disabled:pointer-events-none disabled:opacity-50"
                            :disabled="offer.stock < 1"
                            @click="addToCart"
                        >
                            <i class="ti ti-plus"></i><span>Add</span>
                        </button>
                    </div>
                    <small :class="offer.stock > 0 ? 'text-green-700' : 'text-red-600'">
                        {{ offer.stock > 0 ? `${offer.stock} in stock` : 'Out of stock' }}
                    </small>
                </div>
            </div>
        </div>
    </article>
</template>
