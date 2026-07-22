<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import ProductsNav from '../components/ProductsNav.vue';
import productCategoryRoutes from '@/routes/product-categories';

interface ProductCategory {
    id: number;
    name: string;
    slug: string;
};

interface Props {
    categories: ProductCategory[];
    search?: string;
};

const props = defineProps<Props>();

const search = ref(props.search || '');

const debouncedSearch = useDebounceFn(() => {
    router.get(productCategoryRoutes.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search], () => {
    debouncedSearch();
});
</script>

<template>
    <Head title="Product Categories" />

    <ProductsNav current-page="product-categories" />

    <div class="app-header">
        <h1>Categories</h1>

        <div class="search">
            <input v-model="search" type="text" placeholder="Search categories by name, slug...">
        </div>

        <div class="action">
            <Link :href="productCategoryRoutes.create().url">
                <Button>New Category</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Slug</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(category, index) in props.categories" :key="category.id">
                    <TableCell class="id">{{ index + 1 }}</TableCell>
                    <TableCell>{{ category.name }}</TableCell>
                    <TableCell>{{ category.slug }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="productCategoryRoutes.edit(category.id).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog :url="productCategoryRoutes.destroy(category.id).url" title="Delete Category?" description="This category will be deleted permanently!" confirm-text="Delete Category">
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="props.categories.length === 0">
                    <TableCell colspan="5" class="blank-table-row">
                        No categories found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>