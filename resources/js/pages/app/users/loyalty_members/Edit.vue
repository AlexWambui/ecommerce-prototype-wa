<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import FormHeader from '@/components/custom/FormHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import loyaltyMemberRoutes from '@/routes/loyalty-members';

interface LoyaltyMember {
    id: number;
    uuid: string;
    name: string;
    phone_number: string;
    loyalty_id: number;
    points: number;
}

const props = defineProps<{
    loyalty_member: LoyaltyMember
}>();

const form = useForm({
    name: props.loyalty_member.name,
    phone_number: props.loyalty_member.phone_number,
    points: props.loyalty_member.points,
})

const submitForm = () => {
    form.put(loyaltyMemberRoutes.update.url(props.loyalty_member.uuid), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Edit Location" />

    <div class="form">
        <FormHeader :backUrl="loyaltyMemberRoutes.index().url" title="Edit Location" />

        <form @submit.prevent="submitForm">
            <div class="inputs-group">
                <Label for="name" class="required">Name</Label>
                <Input type="text" id="name" v-model="form.name" autocomplete="name" placeholder="Name" autofocus />
                <InputError :message="form.errors.name" />
            </div>

            <div class="inputs-group">
                <Label for="phone_number" class="required">Phone Number</Label>
                <Input type="text" id="phone_number" v-model="form.phone_number" autocomplete="phone_number" placeholder="0746xxxxxx" />
                <InputError :message="form.errors.phone_number" />
            </div>

            <div class="inputs-group">
                <Label for="points" class="required">Points</Label>
                <Input type="text" id="points" v-model="form.points" autocomplete="points" placeholder="10" />
                <InputError :message="form.errors.points" />
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Update Member
                </Button>

                <div>
                    <Link :href="loyaltyMemberRoutes.index().url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>
