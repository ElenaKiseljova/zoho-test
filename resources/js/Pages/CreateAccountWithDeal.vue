<script setup>
import Form from "@/Components/Form.vue";
import { Link } from "@inertiajs/vue3";
import { onMounted, toRefs, watch } from "vue";
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
});

const { errors, message } = toRefs(props);

// Get toast interface
const toast = useToast();

watch(
  errors,
  () => {
    displayErrors();
  },
  { deep: 1 }
);

watch(message, () => {
  displayMessage();
});

onMounted(() => {
  displayErrors();
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
  <div class="flex items-center flex-col">
    <div v-if="!$attrs.hasTokens" class="">
      <Link href="/refresh-token" method="post">Refresh Token</Link>

      <br />
    </div>

    <!-- For test reason START -->
    <div class="">
      <Link href="/access-token" method="put">Update Access Token</Link>

      <br />
    </div>

    <div class="">
      <Link href="/clear-tokens" method="delete">Clear Tokens</Link>

      <br />
    </div>
    <!-- For test reason END -->

    <Form class="w-[500px]" />
  </div>
</template>

<style lang="scss" scoped></style>
