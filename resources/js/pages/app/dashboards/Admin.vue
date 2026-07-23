<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

interface Props {
    stats: {
        total_customers: number;
        total_users: number;
        total_products: number;
        total_product_categories: number;
        total_delivery_locations: number;
        total_delivery_areas: number;
        total_callbacks: number;
        total_unread_callbacks: number;
    }
};

const props = defineProps<Props>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="Dashboard AdminDashboard space-y-12">
        <div class="header">
            <div class="flex items-center gap-4">
                <p>Hi {{ user.name }}</p>
                <span class="text-xs text-blue-900 bg-blue-100 py-1 px-2 rounded-sm">{{ user.role_label }}</span>
            </div>
        </div>

        <div class="stats-wrapper">
            <h2 class="mb-4 font-medium">Platform Statistics</h2>
            <div class="stats grid gap-8 lg:grid-cols-6">
                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ stats.total_customers }}</p>
                    <p>Customers</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_users }} Users</span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ stats.total_products }}</p>
                    <p>Products</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_product_categories }} Categories</span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ stats.total_delivery_locations }}</p>
                    <p>Locations</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_delivery_areas }} Areas</span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ stats.total_callbacks }}</p>
                    <p>Callback Requests</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_unread_callbacks }} Unread</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>