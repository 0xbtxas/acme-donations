<template>
    <div class="max-w-3xl mx-auto p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Payment methods</h2>
            <UButton :to="{ name: 'home' }" variant="soft">Back</UButton>
        </div>

        <!-- Existing Payment Methods -->
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

        <!-- Add Payment Method Form -->
        <div class="border rounded p-4 space-y-3">
            <div class="font-semibold">Add Payment Method</div>

            <!-- Step 1: Setup Intent -->
            <div v-if="!setupIntent" class="space-y-3">
                <UInput
                    v-model="provider"
                    placeholder="Provider (e.g., stripe)"
                />
                <UButton @click="createSetupIntent" :loading="creatingIntent">
                    Create Setup Intent
                </UButton>
            </div>

            <!-- Step 2: Payment Form -->
            <div v-if="setupIntent && !paymentMethodId" class="space-y-3">
                <div class="text-sm text-gray-600">
                    Setup Intent created. Now add your payment details:
                </div>

                <!-- Payment Form Fields -->
                <div class="space-y-3">
                    <UInput v-model="cardNumber" placeholder="Card Number" />
                    <div class="grid grid-cols-2 gap-2">
                        <UInput v-model="expMonth" placeholder="MM" />
                        <UInput v-model="expYear" placeholder="YYYY" />
                    </div>
                    <UInput v-model="cvc" placeholder="CVC" />
                    <UInput
                        v-model="label"
                        placeholder="Label (e.g., Personal Visa)"
                    />
                    <div class="flex items-center gap-2">
                        <UCheckbox v-model="isDefault" />
                        <span>Make default</span>
                    </div>
                </div>

                <UButton @click="confirmPaymentMethod" :loading="confirming">
                    Confirm Payment Method
                </UButton>
            </div>

            <!-- Step 3: Success -->
            <div v-if="paymentMethodId" class="text-green-600">
                Payment method added successfully!
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";

const items = ref([]);
const provider = ref("stripe");
const setupIntent = ref(null);
const paymentMethodId = ref(null);

// Payment form fields
const cardNumber = ref("");
const expMonth = ref("");
const expYear = ref("");
const cvc = ref("");
const label = ref("");
const isDefault = ref(true);

// Loading states
const creatingIntent = ref(false);
const confirming = ref(false);

// Toast notifications
const toast = useToast();

const fetch = async () => {
    const { data } = await axios.get("/api/payment-methods");
    items.value = data.data;
};

const createSetupIntent = async () => {
    if (!provider.value) {
        toast.add({
            title: "Error",
            description: "Please select a provider",
            color: "red",
        });
        return;
    }

    creatingIntent.value = true;
    try {
        const { data } = await axios.post("/api/payment-methods/setup-intent", {
            provider: provider.value,
        });

        setupIntent.value = data;
        toast.add({
            title: "Success",
            description: "Setup intent created successfully",
            color: "green",
        });
    } catch (error) {
        toast.add({
            title: "Error",
            description:
                error.response?.data?.error || "Failed to create setup intent",
            color: "red",
        });
    } finally {
        creatingIntent.value = false;
    }
};

const confirmPaymentMethod = async () => {
    if (!cardNumber.value || !expMonth.value || !expYear.value || !cvc.value) {
        toast.add({
            title: "Error",
            description: "Please fill in all required fields",
            color: "red",
        });
        return;
    }

    confirming.value = true;
    try {
        // In a real implementation, you would use Stripe.js Elements to collect card details
        // and confirm the setup intent. For now, we'll simulate the confirmation process.
        //
        // The actual flow would be:
        // 1. Use Stripe.js to collect card details securely
        // 2. Confirm the setup intent with Stripe
        // 3. Get the payment_method_id from the confirmation
        // 4. Send that ID to our backend

        const mockPaymentMethodId =
            "pm_" + Math.random().toString(36).substr(2, 9);

        // Send the payment method ID to our backend
        await axios.post("/api/payment-methods", {
            provider: provider.value,
            payment_method_id: mockPaymentMethodId,
            label: label.value,
            is_default: isDefault.value,
        });

        paymentMethodId.value = mockPaymentMethodId;
        toast.add({
            title: "Success",
            description: "Payment method added successfully",
            color: "green",
        });

        // Reset form
        resetForm();
        await fetch();
    } catch (error) {
        toast.add({
            title: "Error",
            description:
                error.response?.data?.error || "Failed to add payment method",
            color: "red",
        });
    } finally {
        confirming.value = false;
    }
};

const resetForm = () => {
    setupIntent.value = null;
    paymentMethodId.value = null;
    cardNumber.value = "";
    expMonth.value = "";
    expYear.value = "";
    cvc.value = "";
    label.value = "";
    isDefault.value = true;
};

onMounted(fetch);
</script>
