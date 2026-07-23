<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ProductGrid from '@/pages/guest/components/ProductGrid.vue';

interface ProductCategory {
	id: number;
	name: string;
	thumbnail_url: string;
}

interface Props {
    product_categories: {
		data: ProductCategory[];
	};
	new_arrivals: any[];
	most_popular: any[];
    search?: string;
};

const props = defineProps<Props>();
</script>

<template>
    <div class="HomePage">
		<section class="Hero mb-15 min-h-[30dvh] flex items-center justify-center lg:min-h-[40dvh]">
			<div class="container-fluid">
				<div class="content text-center space-y-12">
					<h1 class="title font-bold text-l-text lg:text-xl-text uppercase">Step in Style.</h1>
					<div class="actions bg-amber-600 text-white font-semibold py-4 px-8 inline-block rounded-sm">
						<Link href="/shop">Start Shopping</Link>
					</div>
				</div>
			</div>

			<!-- <div class="container-fluid grid lg:grid-cols-12 gap-8">
				<div class="content lg:col-span-7 space-y-4">
					<h1 class="title font-bold text-l-text lg:text-xl-text uppercase">Walk with confidence. <br> Step in Style.</h1>
					<div class="actions bg-amber-400 font-semibold py-2 px-4 inline-block rounded-sm">
						<Link href="/shop">Start Shopping</Link>
					</div>
				</div>

				<div class="image lg:col-span-5 h-[30dvh] lg:h-full">
					<img src='/assets/images/air-force-sneaker-hero-image.png' alt="Air Force shoe hero image" class="object-contain">
				</div>
			</div> -->
		</section>

		<section class="Categories mb-15">
			<div class="container-fluid">
				<div class="categories-wrapper flex flex-wrap gap-16 justify-center">
					<div class="category flex flex-col justify-center items-center gap-2" v-for="category in product_categories.data">
						<div class="image w-25 h-25 rounded-lg border border-border">
							<img :src="category.thumbnail_url" :alt="category.name" width="60" height="60">
						</div>
						<p class="font-medium flex-1">{{ category.name }}</p>
					</div>
				</div>
			</div>
		</section>

		<section class="NewArrivals mb-15">
			<ProductGrid 
				v-if="new_arrivals?.length"
				title="New Arrivals" 
				:products="new_arrivals" 
				:columns="4"
				class="NewArrivals"
			/>
		</section>

		<section class="MostPopular mb-15">
			<ProductGrid 
				title="Most Popular" 
				:products="most_popular"
				:columns="4"
				class="MostPopular"
			/>
		</section>
	</div>
</template>