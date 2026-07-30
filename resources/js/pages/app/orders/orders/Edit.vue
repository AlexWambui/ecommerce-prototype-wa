<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3';
import FormHeader from '@/components/custom/FormHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import orderRoutes from '@/routes/orders';

interface OrderItem {
    id: number;
    name: string;
    quantity: number;
    selling_price: number;
}

interface Payment {
    id: number;
    payment_method: string;
    payment_status: string;
    amount: number;
}

interface Order {
    id: number;
    uuid: string;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_email: string;
    delivery_location: string;
    delivery_area: string;
    delivery_address: string;
    notes: string;
    payment_status: string;
    payment_method: string;
    delivery_status: string;
    sold_at: string;
    order_items: OrderItem[];
    payments: Payment[];
    delivery_cost: number;
    subtotal: number;
    total_amount: number;
    amount_paid: number;
}

const props = defineProps<{
    order: {
        data: Order
    };
}>();

const form = useForm({
    delivery_status: props.order.data.delivery_status,
    notes: props.order.data.notes,
    _method: 'PUT',
});

const submitForm = () => {
    form.post(
        orderRoutes.update({
            order: props.order.data.uuid
        }).url,
        {
            forceFormData: true,
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head title="Edit Order" />

    <div class="order_details grid lg:grid-cols-2 gap-8">
        <div class="details-section">
            <p class="font-bold text-subheading-text">Order Details</p>
            <p class="flex">
                <span class="text-muted-foreground w-30">Order Number</span>
                <span>:{{ order.data.order_number }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Name</span>
                <span>{{ order.data.customer_name }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Phone</span>
                <span>{{ order.data.customer_phone }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Email</span>
                <span>{{ order.data.customer_email ?? 'na' }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Location</span>
                <span>{{ order.data.delivery_location }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Location</span>
                <span>{{ order.data.delivery_area }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Address</span>
                <span>{{ order.data.delivery_address }}</span>
            </p>

            <p class="flex">
                <span class="text-muted-foreground w-30">Date</span>
                <span>{{ order.data.sold_at }}</span>
            </p>
        </div>

        <div class="extras">
            <div class="order-items">
                <p class="font-bold text-subheading-text">Items Ordered</p>
                <div v-for="item in order.data.order_items" :key="item.id" class="items mb-4">
                    <div class="item flex gap-4">
                        <span>{{ item.name }}</span>
                        <span>{{ item.quantity }} @ {{ item.selling_price }}</span>
                        <span>= Ksh. {{ item.quantity * item.selling_price }}</span>
                    </div>
                </div>
                <p class="flex">
                    <span class="text-muted-foreground w-30">Items Total</span>
                    <span>:{{ order.data.subtotal }}</span>
                </p>
                <p class="flex">
                    <span class="text-muted-foreground w-30">Delivery Cost</span>
                    <span>:{{ order.data.delivery_cost }}</span>
                </p>
                <p class="flex">
                    <span class="text-muted-foreground w-30">Total</span>
                    <span>:{{ order.data.total_amount }}</span>
                </p>
            </div>

            <div class="payment-info mt-12">
                <p class="font-bold text-subheading-text">Payments Summary</p>
                <p class="flex">
                    <span class="text-muted-foreground w-30">Amount Paid</span>
                    <span>:{{ order.data.amount_paid }}</span>
                </p>
                <p class="flex">
                    <span class="text-muted-foreground w-30">Payment Status</span>
                    <span>:{{ order.data.payment_status }}</span>
                </p>

                <p class="mt-4">Payments Made</p>
                <p v-for="payment in order.data.payments" :key="payment.id" class="flex">
                    <span class="text-muted-foreground w-30">{{ payment.payment_method }}</span>
                    <span>{{ payment.amount }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="form min-w-full px-0.5">
        <FormHeader :backUrl="orderRoutes.index().url" title="Edit Order" />

        <form @submit.prevent="submitForm">
            <input type="hidden" name="_method" value="PUT" />

            <div class="inputs-group">
                <Label for="delivery_status">Delivery Status</Label>
                <Select v-model="form.delivery_status">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="shipped">Shipped</SelectItem>
                            <SelectItem value="shipped">Delivered</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.delivery_status" />
            </div>

            <div class="inputs-group">
                <Label for="notes">Notes</Label>
                <Textarea
                    id="notes"
                    v-model="form.notes"
                    rows="4"
                    placeholder="Describe your notes..."
                />
                <InputError :message="form.errors.notes" />
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Update Order
                </Button>

                <div>
                    <Link :href="orderRoutes.index().url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>