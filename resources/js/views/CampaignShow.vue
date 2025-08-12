<template>
    <div class="max-w-3xl mx-auto p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">{{ item?.title }}</h2>
            <div class="flex gap-2">
                <UButton
                    variant="soft"
                    :to="{ name: 'campaigns.edit', params: { id } }"
                    >Edit</UButton
                >
                <UButton variant="soft" :to="{ name: 'campaigns' }"
                    >Back</UButton
                >
            </div>
        </div>
        <p class="text-gray-700">{{ item?.description }}</p>
        <div class="text-sm">Status: {{ item?.status }}</div>
        <div class="text-sm">
            Goal: {{ item?.goal_amount }} {{ item?.currency }}
        </div>
        <div class="pt-4">
            <h3 class="font-semibold mb-2">Donate</h3>
            <div class="flex gap-2 items-center">
                <UInput
                    v-model.number="donateAmount"
                    type="number"
                    step="0.01"
                    placeholder="Amount"
                />
                <USelect
                    v-model="paymentMethodId"
                    :options="paymentOptions"
                    placeholder="Payment method"
                />
                <UButton :loading="donating" @click="donate">Donate</UButton>
            </div>
            <div v-if="confirmation" class="mt-3 text-sm">
                <div
                    v-if="confirmation.status === 'confirmed'"
                    class="text-green-600"
                >
                    Thank you! Donation confirmed. Reference:
                    {{ confirmation.external_reference || "N/A" }}
                </div>
                <div
                    v-else-if="confirmation.status === 'failed'"
                    class="text-red-600"
                >
                    Donation failed. Please try again later.
                </div>
                <div v-else class="text-gray-600">Donation submitted.</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const id = route.params.id;
const item = ref(null);
const donateAmount = ref(10);
const donating = ref(false);
const confirmation = ref(null);
let pollTimer = null;
const paymentMethodId = ref(null);
const paymentOptions = ref([]);

const fetch = async () => {
    const { data } = await axios.get(`/api/campaigns/${id}`);
    item.value = data.data;
};

const fetchPaymentMethods = async () => {
    const { data } = await axios.get(`/api/payment-methods`);
    paymentOptions.value = data.data.map((pm) => ({
        label: pm.label || `${pm.provider}`,
        value: pm.id,
    }));
    const def = data.data.find((pm) => pm.is_default);
    paymentMethodId.value = def
        ? def.id
        : paymentOptions.value[0]?.value || null;
};

const donate = async () => {
    donating.value = true;
    try {
        const { data } = await axios.post("/api/donations", {
            campaign_id: id,
            amount: donateAmount.value,
            currency: item.value.currency,
            payment_method_id: paymentMethodId.value,
        });
        const donation = data.data;
        confirmation.value = {
            status: donation.status,
            external_reference: donation.external_reference,
            id: donation.id,
        };
    } finally {
        donating.value = false;
    }
};

onMounted(async () => {
    await Promise.all([fetch(), fetchPaymentMethods()]);
});
</script>
