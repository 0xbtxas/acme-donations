<template>
    <div class="max-w-5xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Campaigns</h2>
            <UButton to="/campaigns/new">New</UButton>
        </div>
        <div class="mb-4">
            <UInput v-model="q" placeholder="Search..." @input="fetch" />
        </div>
        <div v-if="loading" class="opacity-60">Loading...</div>
        <ul v-else class="space-y-3">
            <li v-for="c in items" :key="c.id" class="border rounded p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-medium">
                            <RouterLink
                                :to="{
                                    name: 'campaigns.show',
                                    params: { id: c.id },
                                }"
                                class="underline"
                            >
                                {{ c.title }}
                            </RouterLink>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ c.description }}
                        </div>
                        <div class="text-sm mt-1">
                            Goal: {{ c.goal_amount }} {{ c.currency }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <UButton
                            variant="soft"
                            :to="{
                                name: 'campaigns.edit',
                                params: { id: c.id },
                            }"
                            >Edit</UButton
                        >
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";

const items = ref([]);
const q = ref("");
const loading = ref(false);

const fetch = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/campaigns", {
            params: { q: q.value },
        });
        items.value = data.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetch);
</script>
