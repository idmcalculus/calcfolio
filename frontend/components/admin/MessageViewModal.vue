<template>
  <div v-if="modelValue && message" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
      <!-- Modal Header -->
      <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Message Details</h3>
        <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="closeModal">
          <!-- Close Icon (Heroicon X) -->
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-4">
         <div>
           <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">From:</strong>
           <span class="text-sm text-gray-900 dark:text-white">{{ message.name }} &lt;{{ message.email }}></span>
         </div>
         <div>
           <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Received:</strong>
           <span class="text-sm text-gray-500 dark:text-gray-400">{{ new Date(message.created_at).toLocaleString() }}</span>
         </div>
         <div>
           <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject:</strong>
           <span class="text-sm text-gray-900 dark:text-white">{{ message.subject }}</span>
         </div>
         <hr class="dark:border-gray-700">
         <div>
           <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message:</strong>
           <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap bg-gray-50 dark:bg-gray-700 p-3 rounded">
             {{ message.message }}
           </p>
         </div>
         <!-- Add other fields if needed, e.g., original SES status -->
         <!--
         <div>
           <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">SES Status:</strong>
           <span class="text-sm text-gray-500 dark:text-gray-400">{{ message.status }}</span>
         </div>
         -->
      </div>

       <!-- Modal Footer -->
      <div class="flex justify-end p-4 border-t dark:border-gray-700">
        <button class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 text-sm" @click="closeModal">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PropType } from 'vue'; // Use top-level type import

// Define Message type again (or import from a shared types file)
interface Message {
  id: number;
  name: string;
  email: string;
  subject: string;
  message: string;
  status: string;
  message_id: string | null;
  is_read: boolean;
  created_at: string;
  updated_at: string;
}

// Props: modelValue for v-model compatibility, message to display
const props = defineProps({
  modelValue: { // Controls visibility (v-model)
    type: Boolean,
    required: true
  },
  message: { // The message object to display
    type: Object as PropType<Message | null>,
    required: false,
    default: null
  }
});

// Emits: update:modelValue for v-model compatibility
const emit = defineEmits(['update:modelValue']);

const closeModal = () => {
  emit('update:modelValue', false); // Emit event to close the modal
};

// Optional: Close modal on Escape key press
// import { onMounted, onUnmounted } from 'vue';
// const handleEscape = (e: KeyboardEvent) => {
//   if (e.key === 'Escape' && props.modelValue) {
//     closeModal();
//   }
// };
// onMounted(() => window.addEventListener('keydown', handleEscape));
// onUnmounted(() => window.removeEventListener('keydown', handleEscape));

</script>

<style scoped>
/* Add any specific modal styles if needed */
.whitespace-pre-wrap {
  white-space: pre-wrap; /* Ensures message formatting is preserved */
}
</style>
