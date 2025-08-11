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
                <UButton :loading="donating" @click="donate">Donate</UButton>
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

const fetch = async () => {
    const { data } = await axios.get(`/api/campaigns/${id}`);
    item.value = data.data;
};

const donate = async () => {
    donating.value = true;
    try {
        await axios.post("/api/donations", {
            campaign_id: id,
            amount: donateAmount.value,
            currency: item.value.currency,
        });
    } finally {
        donating.value = false;
    }
};

onMounted(fetch);
</script>
