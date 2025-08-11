<template>
    <div class="max-w-xl mx-auto p-6 space-y-4">
        <h2 class="text-xl font-semibold">New Campaign</h2>
        <form @submit.prevent="submit" class="space-y-3">
            <UInput v-model="form.title" placeholder="Title" />
            <UTextarea v-model="form.description" placeholder="Description" />
            <div class="grid grid-cols-2 gap-3">
                <UInput
                    v-model.number="form.goal_amount"
                    type="number"
                    step="0.01"
                    placeholder="Goal amount"
                />
                <UInput v-model="form.currency" placeholder="Currency" />
            </div>
            <div class="flex gap-2">
                <UButton type="submit" :loading="loading">Create</UButton>
                <UButton variant="soft" :to="{ name: 'campaigns' }"
                    >Cancel</UButton
                >
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);
const form = reactive({
    title: "",
    description: "",
    goal_amount: 0,
    currency: "USD",
});

const submit = async () => {
    loading.value = true;
    try {
        const { data } = await axios.post("/api/campaigns", form);
        router.replace({
            name: "campaigns.show",
            params: { id: data.data.id },
        });
    } finally {
        loading.value = false;
    }
};
</script>
