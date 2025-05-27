<script setup>
import { useForm } from "@inertiajs/vue3";

defineProps({
  modelValue: {
    type: [Boolean, null],
    required: false,
  },
  stages: {
    type: [Array, null],
    required: false,
  },
});

const emits = defineEmits(["update:modelValue"]);

const form = useForm({
  Account_Name: null,
  Website: null,
  Phone: null,
  Deal_Name: null,
  Closing_Date: null,
  Stage: null,
});

// Submit form handler
const submitHandler = () => {
  emits("update:modelValue", true);

  form.post("/store-account-with-deal", {
    onSuccess: () => {
      form.reset();
      form.clearErrors();
    },
    onFinish: () => emits("update:modelValue", false),
  });
};

// Input Fields handler
const inputHandler = (evt) => {
  form.clearErrors(evt.target.name);
};
</script>

<template>
  <form class="w-full" @submit.prevent="submitHandler">
    <div class="flex flex-col lg:flex-row">
      <fieldset class="fieldset w-full max-w-xs">
        <legend class="font-bold text-lg">Account</legend>

        <label
          for="Account_Name"
          class="text-gray-600 font-medium text-sm mt-3"
        >
          Name
        </label>
        <input
          id="Account_Name"
          type="text"
          class="input"
          :class="{ 'input-error': form.errors.Account_Name }"
          name="Account_Name"
          placeholder="Best ..."
          v-model="form.Account_Name"
          @input="inputHandler"
        />
        <p v-if="form.errors.Account_Name" class="text-xs text-red-400 h-0">
          {{ form.errors.Account_Name }}
        </p>

        <label for="Website" class="text-gray-600 font-medium text-sm mt-3">
          Website
        </label>
        <input
          id="Website"
          type="url"
          class="input"
          :class="{ 'input-error': form.errors.Website }"
          name="Website"
          placeholder="https:// ..."
          v-model="form.Website"
          @input="inputHandler"
        />
        <p v-if="form.errors.Website" class="text-xs text-red-400 h-0">
          {{ form.errors.Website }}
        </p>

        <label for="Phone" class="text-gray-600 font-medium text-sm mt-3">
          Phone
        </label>
        <input
          id="Phone"
          type="tel"
          class="input"
          :class="{ 'input-error': form.errors.Phone }"
          name="Phone"
          placeholder="+38 ..."
          v-model="form.Phone"
          @input="inputHandler"
        />
        <p v-if="form.errors.Phone" class="text-xs text-red-400 h-0">
          {{ form.errors.Phone }}
        </p>
      </fieldset>

      <div class="divider lg:divider-horizontal"></div>

      <fieldset class="fieldset w-full max-w-xs">
        <legend class="font-bold text-lg">Deal</legend>

        <label for="Deal_Name" class="text-gray-600 font-medium text-sm mt-3">
          Name
        </label>
        <input
          id="Deal_Name"
          type="text"
          class="input"
          :class="{ 'input-error': form.errors.Deal_Name }"
          name="Deal_Name"
          placeholder="Great ..."
          v-model="form.Deal_Name"
          @input="inputHandler"
        />
        <p v-if="form.errors.Deal_Name" class="text-xs text-red-400 h-0">
          {{ form.errors.Deal_Name }}
        </p>

        <label
          for="Closing_Date"
          class="text-gray-600 font-medium text-sm mt-3"
        >
          Closing Date
        </label>
        <input
          id="Closing_Date"
          type="date"
          class="input"
          :class="{ 'input-error': form.errors.Closing_Date }"
          name="Closing_Date"
          v-model="form.Closing_Date"
          @input="inputHandler"
        />
        <p v-if="form.errors.Closing_Date" class="text-xs text-red-400 h-0">
          {{ form.errors.Closing_Date }}
        </p>

        <template v-if="stages?.length">
          <label for="Stage" class="text-gray-600 font-medium text-sm mt-3">
            Stage
          </label>
          <select
            id="Stage"
            class="select"
            :class="{ 'select-error': form.errors.Stage }"
            name="Stage"
            v-model="form.Stage"
            @change="inputHandler"
          >
            <option disabled value="null">Pick a Stage</option>
            <option v-for="stage in stages" :key="stage.id" :value="stage.name">
              {{ stage.name }}
            </option>
          </select>
          <p v-if="form.errors.Stage" class="text-xs text-red-400 h-0">
            {{ form.errors.Stage }}
          </p>
        </template>
      </fieldset>
    </div>

    <button class="btn btn-neutral mt-4" :disabled="form.processing">
      Create
      <span
        v-if="form.processing"
        class="loading loading-ring loading-xs"
      ></span>
    </button>
  </form>
</template>

<style lang="scss" scoped></style>
