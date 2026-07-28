<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import TableResultsSummary from '@/components/custom/Tables/ResultsSummary.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePriceFormatter } from '@/composables/usePriceFormatter';
import DeliveryAreaRoutes from '@/routes/delivery-areas';
import DeliveryLocationRoutes from '@/routes/delivery-locations';

const { formatPrice } = usePriceFormatter();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Delivery Locations', href: DeliveryLocationRoutes.index() },
            { title: 'Delivery Areas', description: 'Delivery Areas' }
        ]
    }
});

interface DeliveryLocation {
    id: number;
    uuid: string;
    name: string;
}

interface DeliveryArea {
    id: number;
    uuid: string;
    name: string;
    price: number;
    estimated_days: number;
    is_active: boolean;
};

interface Props {
    delivery_location: DeliveryLocation;

    delivery_areas: {
        data: DeliveryArea[];
        links: any[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            links: any[];
        }
    }

    search?: string;
};

const props = defineProps<Props>();

const search = ref(props.search || '');
const debouncedSearch = useDebounceFn(() => {
    router.get(DeliveryLocationRoutes.show(props.delivery_location.uuid).url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);
watch([search], () => {
    debouncedSearch();
});

const hasActiveFilters = computed(() =>
    !!(search.value)
);
</script>

<template>
    <Head title="Delivery Areas" />

    <div class="app-header">
        <div class="info">
            <h1 class="title">Delivery Areas - {{ delivery_location.name }}</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name or slug"
            />
        </div>

        <div class="action">
            <Link :href="DeliveryAreaRoutes.create(delivery_location.uuid).url">
                <Button>New Area</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Shipping Cost</TableHead>
                    <TableHead>Estimated Days</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(area, index) in delivery_areas.data" :key="area.id">
                    <TableCell class="id">{{ (delivery_areas.meta.current_page - 1) * delivery_areas.meta.per_page + index + 1 }}</TableCell>
                    <TableCell :class="{'text-red-600' : !area.is_active}">{{ area.name }}</TableCell>
                    <TableCell>{{ formatPrice(area.price) }}</TableCell>
                    <TableCell>{{ area.estimated_days }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="DeliveryAreaRoutes.edit({delivery_location: delivery_location.uuid, delivery_area: area.uuid}).url" class="action edit">
                                <Pencil class="icon edit" />
                            </Link>

                            <span class="divider">|</span>

                            <DeleteConfirmationDialog :url="DeliveryAreaRoutes.destroy(area.uuid).url" title="Delete Area?" description="This area will be deleted permanently!" confirm-text="Delete Area">
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 class="icon delete" />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="delivery_areas.data.length === 0">
                    <TableCell colspan="5" class="blank-table-row">
                        No areas found!
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="delivery_areas.meta" />

    <TableResultsSummary
        :meta="delivery_areas.meta"
        item-name="delivery area"
        item-name-plural="delivery areas"
        :show-filter-indicators="true"
        :has-active-filters="hasActiveFilters"
    />
</template>
