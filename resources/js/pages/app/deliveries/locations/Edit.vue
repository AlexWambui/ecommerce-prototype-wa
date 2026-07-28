<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import deliveryLocationRoutes from '@/routes/delivery-locations';
import FormHeader from '@/components/custom/FormHeader.vue';

interface DeliveryLocation {
    id: number;
    uuid: string;
    name: string;
};

const props = defineProps<{
    delivery_location: DeliveryLocation
}>();

const form = useForm({
    name: props.delivery_location.name,
})

const submitForm = () => {
    form.put(deliveryLocationRoutes.update.url(props.delivery_location.uuid), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Edit Location" />

    <div class="form">
        <FormHeader :backUrl="deliveryLocationRoutes.index().url" title="Edit Location" />

        <form @submit.prevent="submitForm">
            <div class="inputs-group-wrapper">
                <div class="inputs-group">
                    <Label for="name" class="required">Name</Label>
                    <Input type="text" id="name" v-model="form.name" autocomplete="name" placeholder="Name" autofocus />
                    <InputError :message="form.errors.name" />
                </div>
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Update Location
                </Button>

                <div>
                    <Link :href="deliveryLocationRoutes.index().url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>
