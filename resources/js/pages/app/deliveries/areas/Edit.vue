<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import FormHeader from '@/components/custom/FormHeader.vue';
import deliveryAreaRoutes from '@/routes/delivery-areas';
import deliveryLocationRoutes from '@/routes/delivery-locations';

interface DeliveryArea {
    id: number;
    name: string;
    uuid: string;
    estimated_days: number;
    price: number;
    is_active: boolean;
    delivery_location_id: number;
};

interface DeliveryLocation {
    id: number;
    uuid: string;
    name: string;
}

const props = defineProps<{
    delivery_area: DeliveryArea
    delivery_location: DeliveryLocation
}>();

const form = useForm({
    name: props.delivery_area.name,
    estimated_days: props.delivery_area.estimated_days,
    price: props.delivery_area.price,
    is_active: props.delivery_area.is_active,
    delivery_location_id: props.delivery_location.id
})

const submitForm = () => {
    form.put(deliveryAreaRoutes.update.url({delivery_location: props.delivery_location.uuid, delivery_area: props.delivery_area.uuid}), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Edit Delivery Area" />

    <div class="form">
        <FormHeader :backUrl="deliveryLocationRoutes.show(delivery_location.uuid).url" title="Edit Area" />

        <form @submit.prevent="submitForm">
            <div class="inputs-group-wrapper">
                <div class="inputs-group">
                    <Label for="name" class="required">Name</Label>
                    <Input type="text" id="name" v-model="form.name" autocomplete="name" placeholder="Name" autofocus />
                    <InputError :message="form.errors.name" />
                </div>
            </div>

            <div class="inputs-group-wrapper">
                <div class="inputs-group">
                    <Label for="estimated_days" class="required">Estimated Days</Label>
                    <Input type="number" id="estimated_days" v-model="form.estimated_days" autocomplete="estimated_days" placeholder="2" />
                    <InputError :message="form.errors.estimated_days" />
                </div>
            </div>

            <div class="inputs-group-wrapper">
                <div class="inputs-group">
                    <Label for="price" class="required">Shipping Cost</Label>
                    <Input type="number" id="price" v-model="form.price" autocomplete="price" placeholder="1000" />
                    <InputError :message="form.errors.price" />
                </div>
            </div>

            <div class="inputs-group">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0" />
                    <input
                        type="checkbox"
                        id="is_active"
                        v-model="form.is_active"
                        value="1"
                        class="w-4 h-4 rounded"
                    />
                    <Label for="is_active">Active</Label>
                </div>
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Update Delivery Area
                </Button>

                <div>
                    <Link :href="deliveryLocationRoutes.show(delivery_location.uuid).url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>
