<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, ArcElement, PointElement } from 'chart.js';
import { computed } from 'vue';
import { Line, Pie } from 'vue-chartjs';
import { usePriceFormatter } from '@/composables/usePriceFormatter';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, ArcElement, PointElement);

const page = usePage();
const user = computed(() => page.props.auth.user);
const {formatPrice} = usePriceFormatter();

interface Props {
    stats: {
        total_customers: number;
        total_users: number;
        total_products: number;
        total_product_categories: number;
        total_brands: number;
        total_delivery_locations: number;
        total_delivery_areas: number;
        total_callbacks: number;
        total_unread_callbacks: number;
        total_orders: number;
        total_pending_orders: number;
        // NEW CHART DATA
        monthly_sales: number[];
        payment_breakdown: {
            mpesa: number;
            cash: number;
        };

        current_ytd_revenue: number;
        previous_ytd_revenue: number;
        growth_percentage: number;
    }
};

const props = defineProps<Props>();

// --- BAR CHART CONFIGURATION ---
const lineChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        {
            label: 'Sales (Ksh)',
            data: props.stats.monthly_sales,
            borderColor: '#3b82f6', // blue-500
            backgroundColor: 'rgba(59, 130, 246, 0.1)', // fill color (light blue)
            borderWidth: 3,
            fill: true,
        }
    ]
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { 
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `Ksh ${context.parsed.y.toLocaleString()}`
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { callback: (value: any) => `${value.toLocaleString()}` }
        }
    }
};

// --- PIE CHART CONFIGURATION ---
const pieChartData = computed(() => ({
    labels: ['M-Pesa', 'Cash'],
    datasets: [
        {
            data: [
                props.stats.payment_breakdown.mpesa, 
                props.stats.payment_breakdown.cash
            ],
            backgroundColor: ['#10b981', '#f59e0b'], // green-500, amber-500
            borderWidth: 1
        }
    ]
}));

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { 
            position: 'right' as const, 
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const value = context.parsed;
                    const dataset = context.dataset;
                    const total = dataset.data.reduce((a: number, b: number) => a + b, 0);

                    if (total === 0) {
                        return `${context.label}: Ksh 0 (0%)`;
                    }

                    const percentage = ((value / total) * 100).toFixed(1);

                    return `Ksh ${value.toLocaleString()} (${percentage}%)`;
                }
            }
        }
    }
};
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
                    <p class="text-[24px] font-bold">{{ stats.total_orders }}</p>
                    <p>Orders</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_pending_orders }} Pending</span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ stats.total_products }}</p>
                    <p>Products</p>
                    <div class="extras">
                        <span class="text-sm text-muted-foreground">{{ stats.total_product_categories }} Categories & {{ stats.total_brands }} Brands</span>
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

        <div class="stats-wrapper">
            <h2 class="mb-4 font-medium">Fiscal Statistics in Ksh. (2026)</h2>
            <div class="stats grid gap-8 lg:grid-cols-4">
                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">{{ formatPrice(stats.current_ytd_revenue) ?? 0 }}</p>
                    <p>Total Revenue</p>
                    <div class="extras">
                        <span 
                            class="text-sm text-muted-foreground font-medium"
                            :class="stats.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ stats.growth_percentage >= 0 ? '↑' : '↓' }} {{ Math.abs(stats.growth_percentage).toFixed(1) }}% vs. last year
                        </span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">1001</p>
                    <p title="Revenue - COGS">Gross Profit</p>
                    <div class="extras">
                        <span 
                            class="text-sm text-muted-foreground font-medium"
                            :class="stats.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            1001% vs. last year
                        </span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border">
                    <p class="text-[24px] font-bold">1001</p>
                    <p>Operating Expenses</p>
                    <div class="extras">
                        <span 
                            class="text-sm text-muted-foreground font-medium"
                            :class="stats.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            1001% vs. last year
                        </span>
                    </div>
                </div>

                <div class="stat p-4 rounded-lg space-y-0.5 border border-border text-green-600">
                    <p class="text-[24px] font-bold">1001</p>
                    <p title="Gross Profit - Expenses">Net Profit</p>
                    <div class="extras">
                        <span 
                            class="text-sm text-muted-foreground font-medium"
                            :class="stats.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            1001% vs. last year
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION -->
        <div class="charts-wrapper grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart (Spans 2 columns) -->
            <div class="chart-card bg-background p-4 rounded-lg border border-border lg:col-span-2 h-80">
                <h3 class="text-sm font-medium mb-2">Sales Performance in Ksh. ({{ new Date().getFullYear() }})</h3>
                <div class="h-65">
                    <Line :data="lineChartData" :options="lineChartOptions" style="height: 100%!important; width: 100%!important;" />
                </div>
            </div>

            <!-- Pie Chart (Spans 1 column) -->
            <div class="chart-card bg-background p-4 rounded-lg border border-border h-80">
                <h3 class="text-sm font-medium mb-2">Payment Methods</h3>
                <div class="h-65">
                    <Pie :data="pieChartData" :options="pieChartOptions" style="height: 100%!important; width:100%!important" />
                </div>
            </div>
        </div>
    </div>
</template>