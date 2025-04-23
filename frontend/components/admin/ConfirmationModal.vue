<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
      <!-- Modal Header -->
      <div class="p-4 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
      </div>

      <!-- Modal Body -->
      <div class="p-6">
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ message }}</p>
      </div>

       <!-- Modal Footer -->
      <div class="flex justify-end space-x-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
        <button @click="cancel" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
          {{ cancelText }}
        </button>
         <button @click="confirm" :class="confirmButtonClass" class="px-4 py-2 text-white rounded text-sm">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  modelValue: { // v-model for visibility
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmVariant: { // e.g., 'danger', 'primary', 'warning'
    type: String,
    default: 'primary'
  }
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const closeModal = () => {
  emit('update:modelValue', false);
};

const confirm = () => {
  emit('confirm');
  closeModal();
};

const cancel = () => {
  emit('cancel');
  closeModal();
};

// Determine button color based on variant
const confirmButtonClass = computed(() => {
  switch (props.confirmVariant) {
    case 'danger':
      return 'bg-red-600 hover:bg-red-700';
    case 'warning':
      return 'bg-yellow-500 hover:bg-yellow-600';
    case 'primary':
    default:
      return 'bg-primary hover:bg-red-600'; // Assuming primary is defined in Tailwind config
  }
});

</script>
