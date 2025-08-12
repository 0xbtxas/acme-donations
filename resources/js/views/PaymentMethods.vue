<template>
    <div class="max-w-3xl mx-auto p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Payment methods</h2>
            <UButton :to="{ name: 'home' }" variant="soft">Back</UButton>
        </div>
        <div class="space-y-2">
            <div
                v-for="pm in items"
                :key="pm.id"
                class="border rounded p-3 flex items-center justify-between"
            >
                <div>
                    <div class="font-medium">{{ pm.label || pm.provider }}</div>
                    <div class="text-xs text-gray-500">
                        {{ pm.provider }} • {{ pm.brand || "token" }}
                        {{ pm.last4 ? "••••" + pm.last4 : "" }}
                    </div>
                </div>
                <div>
                    <UBadge v-if="pm.is_default" color="green">Default</UBadge>
                </div>
            </div>
        </div>
        <form @submit.prevent="add" class="border rounded p-4 space-y-3">
            <div class="font-semibold">Add method</div>
            <UInput v-model="provider" placeholder="Provider (e.g., stripe)" />
            <UInput
                v-model="providerToken"
                placeholder="Provider method token"
            />
            <div class="grid grid-cols-3 gap-2">
                <UInput v-model="brand" placeholder="Brand (e.g., Visa)" />
                <UInput v-model="last4" placeholder="Last4" />
                <div class="flex gap-2">
                    <UInput v-model.number="expMonth" placeholder="MM" />
                    <UInput v-model.number="expYear" placeholder="YYYY" />
                </div>
            </div>
            <UInput v-model="label" placeholder="Label (e.g., Personal Visa)" />
            <div class="flex items-center gap-2">
                <UCheckbox v-model="isDefault" /> <span>Make default</span>
            </div>
            <UButton type="submit" :loading="submitting">Save</UButton>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";

const items = ref([]);
const provider = ref("stripe");
const providerToken = ref("");
const brand = ref("");
const last4 = ref("");
const expMonth = ref();
const expYear = ref();
const label = ref("");
const isDefault = ref(true);
const submitting = ref(false);

const fetch = async () => {
    const { data } = await axios.get("/api/payment-methods");
    items.value = data.data;
};

const add = async () => {
    submitting.value = true;
    try {
        await axios.post("/api/payment-methods", {
            provider: provider.value,
            payment_method_token: providerToken.value,
            brand: brand.value,
            last4: last4.value,
            exp_month: expMonth.value,
            exp_year: expYear.value,
            label: label.value,
            is_default: isDefault.value,
        });
        providerToken.value = "";
        brand.value = "";
        last4.value = "";
        expMonth.value = undefined;
        expYear.value = undefined;
        label.value = "";
        isDefault.value = false;
        await fetch();
    } finally {
        submitting.value = false;
    }
};

onMounted(fetch);
</script>
