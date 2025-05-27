<script setup>
import Form from "@/Components/Form.vue";
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, toRefs, watch } from "vue";
import { useToast } from "vue-toastification";

const props = defineProps({
  errors: {
    type: [Object, null],
    required: false,
  },
  message: {
    type: [String, null],
    required: false,
  },
  stages: {
    type: [Array, null],
    required: false,
  },
  hasTokens: {
    type: [Boolean, null],
    required: false,
  },
});

const { errors, message } = toRefs(props);

// Get toast interface
const toast = useToast();

// Fleg of Submitting the Form
const isSubmittingForm = ref(false);

watch(
  errors,
  () => {
    if (!isSubmittingForm.value) {
      displayErrors();
    }
  },
  { deep: 1 }
);

watch(message, () => {
  displayMessage();
});

onMounted(() => {
  if (!isSubmittingForm.value) {
    displayErrors();
  }

  displayMessage();
});

// Toasts with error messages
const displayErrors = () => {
  if (typeof errors.value === "object") {
    for (const [key, value] of Object.entries(errors.value)) {
      toast.error(value);
    }
  }
};

// Toast with message
const displayMessage = () => {
  if (message.value) {
    toast.info(message.value);
  }
};
</script>

<template>
  <div class="hero bg-base-200 min-h-screen">
    <div class="hero-content flex-col">
      <div class="text-center lg:text-left">
        <h1 class="text-5xl font-bold">
          Create Account and Deal in Zoho CRM now!
        </h1>
        <p class="py-6">
          If You have changed the Grand Token and want to RESET all tokens - You
          should click the «Clear Tokens» button to display the «Update Refresh
          Token» button.
        </p>

        <div class="flex flex-col items-center gap-2 md:flex-row">
          <Link
            v-if="!hasTokens"
            href="/update-refresh-token"
            class="btn btn-primary"
          >
            Update Refresh Token (once time)
          </Link>

          <Link
            v-if="hasTokens && errors['INVALID_TOKEN']"
            href="/update-access-token"
            class="btn btn-accent"
          >
            Update Access Token
          </Link>

          <Link href="/clear-tokens" method="delete" class="btn btn-secondary">
            Clear Tokens
          </Link>
        </div>
      </div>
      <div class="card bg-base-100 w-full shrink-0 shadow-2xl">
        <div class="card-body">
          <Form
            class="w-full"
            v-model="isSubmittingForm"
            :stages="stages?.data"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped></style>
