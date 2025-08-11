<template>
    <div class="max-w-xl mx-auto p-6 space-y-4">
        <h2 class="text-xl font-semibold">Edit Campaign</h2>
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
                <UButton type="submit" :loading="loading">Save</UButton>
                <UButton
                    variant="soft"
                    @click="publish"
                    :loading="publishing"
                    v-if="canPublish"
                    >Publish</UButton
                >
                <UButton
                    variant="soft"
                    color="red"
                    @click="destroy"
                    :loading="deleting"
                    >Delete</UButton
                >
                <UButton
                    variant="soft"
                    :to="{ name: 'campaigns.show', params: { id } }"
                    >Cancel</UButton
                >
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();
const id = route.params.id;
const loading = ref(false);
const publishing = ref(false);
const deleting = ref(false);
const form = reactive({
    title: "",
    description: "",
    goal_amount: 0,
    currency: "USD",
    status: "draft",
});

const fetch = async () => {
    const { data } = await axios.get(`/api/campaigns/${id}`);
    Object.assign(form, data.data);
};

const submit = async () => {
    loading.value = true;
    try {
        await axios.put(`/api/campaigns/${id}`, {
            title: form.title,
            description: form.description,
            goal_amount: form.goal_amount,
            currency: form.currency,
        });
        router.replace({ name: "campaigns.show", params: { id } });
    } finally {
        loading.value = false;
    }
};

const publish = async () => {
    publishing.value = true;
    try {
        await axios.post(`/api/campaigns/${id}/publish`);
        await fetch();
    } finally {
        publishing.value = false;
    }
};

const destroy = async () => {
    deleting.value = true;
    try {
        await axios.delete(`/api/campaigns/${id}`);
        router.replace({ name: "campaigns" });
    } finally {
        deleting.value = false;
    }
};

const canPublish = computed(() => form.status !== "published");

onMounted(fetch);
</script>
