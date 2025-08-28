<template>
  <div class="message-table-container p-4">
    <h3 class="text-xl font-semibold mb-4">Inbox Messages</h3>

    <!-- Controls: Filters, Search, etc. -->
    <div class="controls mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded shadow-xs grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Search Input -->
      <div>
        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
        <input
          id="search"
          v-model.lazy="searchTerm"
          type="text"
          placeholder="Search name, email, subject..."
          class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
        >
      </div>

      <!-- Filter Dropdown -->
      <div>
        <label for="filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter Status</label>
        <select
          id="filter"
          v-model="filterRead"
          class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
        >
          <option :value="null">All</option>
          <option value="0">Unread</option>
          <option value="1">Read</option>
        </select>
      </div>

      <!-- Sort Dropdown -->
      <div>
         <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
         <select
           id="sort"
           class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
           @change="updateSort($event)"
         >
           <option value="created_at:desc" :selected="sortBy === 'created_at' && sortOrder === 'desc'">Date Received (Newest)</option>
           <option value="created_at:asc" :selected="sortBy === 'created_at' && sortOrder === 'asc'">Date Received (Oldest)</option>
           <option value="name:asc" :selected="sortBy === 'name' && sortOrder === 'asc'">Name (A-Z)</option>
           <option value="name:desc" :selected="sortBy === 'name' && sortOrder === 'desc'">Name (Z-A)</option>
           <option value="is_read:asc" :selected="sortBy === 'is_read' && sortOrder === 'asc'">Status (Read First)</option>
           <option value="is_read:desc" :selected="sortBy === 'is_read' && sortOrder === 'desc'">Status (Unread First)</option>
           <!-- Add other sort options if needed -->
         </select>
      </div>

      <!-- Bulk Actions (conditionally rendered) -->
      <div v-if="selectedIds.length > 0" class="md:col-span-3 mt-2 flex space-x-2 items-center border-t pt-4 dark:border-gray-600">
         <span class="text-sm text-gray-600 dark:text-gray-300">{{ selectedIds.length }} selected</span>
         <button class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded disabled:opacity-50" :disabled="bulkActionLoading" @click="performBulkAction('mark_read')">Mark Read</button>
         <button class="px-3 py-1 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded disabled:opacity-50" :disabled="bulkActionLoading" @click="performBulkAction('mark_unread')">Mark Unread</button>
         <button class="px-3 py-1 text-sm bg-red-500 hover:bg-red-600 text-white rounded disabled:opacity-50" :disabled="bulkActionLoading" @click="performBulkAction('delete')">Delete</button>
         <span v-if="bulkActionLoading" class="text-sm ml-2">Processing...</span>
         <span v-if="bulkActionError" class="text-sm ml-2 text-red-500">{{ bulkActionError }}</span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="props.loading" class="text-center py-10">
      Loading messages...
    </div>

    <!-- Error State -->
    <div v-else-if="props.error" class="text-center py-10 text-red-500">
      Error loading messages: {{ props.error.message }}
    </div>

    <!-- Message Table -->
    <div v-else-if="paginatedMessages && paginatedMessages.length > 0" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
              <input
                v-model="selectAll"
                type="checkbox"
                title="Select all visible"
                class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
              >
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              From
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Subject
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Received
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Status
            </th>
            <th scope="col" class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="message in paginatedMessages" :key="message.id" :class="[{ 'font-semibold': !message.is_read }, selectedIds.includes(message.id) ? 'bg-blue-50 dark:bg-gray-700' : '']">
            <td class="px-6 py-4 whitespace-nowrap">
               <input
                 v-model="selectedIds"
                 type="checkbox"
                 :value="message.id"
                 class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
               >
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900 dark:text-white">{{ message.name }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ message.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
              {{ message.subject }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ new Date(message.created_at).toLocaleString() }} <!-- Basic Date Formatting -->
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
:class="message.is_read ? 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ message.is_read ? 'Read' : 'Unread' }}
              </span>
            </td>
             <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
               <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" @click="viewMessage(message)">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
     <div v-else class="text-center py-10 text-gray-500">
      No messages found.
    </div>

    <!-- Pagination Controls -->
    <div v-if="pagination && pagination.total > itemsPerPage" class="pagination-controls mt-6 flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 sm:px-6 rounded-b shadow-xs">
      <div class="flex-1 flex justify-between sm:hidden">
        <button
          :disabled="currentPage === 1"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
          @click="prevPage"
        >
          Previous
        </button>
        <button
          :disabled="currentPage === pagination.last_page"
          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
          @click="nextPage"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            Showing
            <span class="font-medium">{{ pagination.from }}</span>
            to
            <span class="font-medium">{{ pagination.to }}</span>
            of
            <span class="font-medium">{{ pagination.total }}</span>
            results
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-xs -space-x-px" aria-label="Pagination">
            <button
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
              @click="prevPage"
            >
              <span class="sr-only">Previous</span>
              <!-- Heroicon name: solid/chevron-left -->
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <!-- Current Page Info (Simple) -->
             <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300">
              Page {{ currentPage }} of {{ pagination.last_page }}
            </span>
            <button
              :disabled="currentPage === pagination.last_page"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
              @click="nextPage"
            >
              <span class="sr-only">Next</span>
              <!-- Heroicon name: solid/chevron-right -->
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Message View Modal -->
    <MessageViewModal v-model="isModalOpen" :message="messageToView" />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      v-model="isConfirmModalOpen"
      :message="confirmModalMessage"
      confirm-variant="danger"
      confirm-text="Delete"
      @confirm="confirmActionCallback && confirmActionCallback()"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import MessageViewModal from './MessageViewModal.vue';
import ConfirmationModal from './ConfirmationModal.vue'; // Import ConfirmationModal
import type { Message } from '~/composables/useApi';

const { admin } = useApi();
const toast = useToast();

// Props from parent component
const props = defineProps<{
  messages: Message[]
  loading: boolean
  error: Error | null
  refresh: () => Promise<void>
}>();

// --- State for fetching and controls ---
const currentPage = ref(1);
const itemsPerPage = ref(15); // Match backend default
const filterRead = ref<string | null>(null); // '0', '1', or null
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const searchTerm = ref('');
const selectedIds = ref<number[]>([]); // State for selected message IDs
const bulkActionLoading = ref(false);
const bulkActionError = ref<string | null>(null);
const isModalOpen = ref(false);
const messageToView = ref<Message | null>(null);
const isConfirmModalOpen = ref(false);
// Adjust type to allow undefined or a function returning void/Promise<void>
const confirmActionCallback = ref<(() => void | Promise<void>) | undefined>(undefined);
const confirmModalMessage = ref('');

// --- Select All Logic ---
const selectAll = computed({
  get: () => paginatedMessages.value.length > 0 && selectedIds.value.length === paginatedMessages.value.length,
  set: (value: boolean) => {
    selectedIds.value = value ? paginatedMessages.value.map((msg: Message) => msg.id) : [];
  }
});

// Use messages from props instead of API call
const allMessages = computed(() => props.messages);

// Paginated messages for display (client-side pagination)
const paginatedMessages = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredMessages.value.slice(start, end);
});

// Client-side filtering and sorting
const filteredMessages = computed(() => {
  let messages = [...allMessages.value];

  // Apply read status filter
  if (filterRead.value !== null) {
    messages = messages.filter(msg => msg.is_read === (filterRead.value === '1'));
  }

  // Apply search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    messages = messages.filter(msg =>
      msg.name.toLowerCase().includes(search) ||
      msg.email.toLowerCase().includes(search) ||
      msg.subject.toLowerCase().includes(search) ||
      msg.message.toLowerCase().includes(search)
    );
  }

  // Apply sorting
  messages.sort((a, b) => {
    let aValue: string | number | boolean = a[sortBy.value as keyof typeof a];
    let bValue: string | number | boolean = b[sortBy.value as keyof typeof b];

    // Handle string comparison
    if (typeof aValue === 'string') {
      aValue = aValue.toLowerCase();
      bValue = (bValue as string).toLowerCase();
    }

    if (sortOrder.value === 'asc') {
      return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
    } else {
      return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
    }
  });

  return messages;
});


// Computed pagination info
const pagination = computed(() => {
  const total = filteredMessages.value.length;
  return {
    total,
    per_page: itemsPerPage.value,
    current_page: currentPage.value,
    last_page: Math.ceil(total / itemsPerPage.value),
    from: total > 0 ? ((currentPage.value - 1) * itemsPerPage.value) + 1 : null,
    to: total > 0 ? Math.min(currentPage.value * itemsPerPage.value, total) : null,
  };
});

// --- Control Functions ---
const updateSort = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  const [newSortBy, newSortOrder] = target.value.split(':');
  sortBy.value = newSortBy || 'created_at';
  sortOrder.value = newSortOrder || 'desc';
  currentPage.value = 1; // Reset to first page when sorting changes
};

// Watchers to reset page number when filters/search change
watch(searchTerm, () => { currentPage.value = 1; });
watch(filterRead, () => { currentPage.value = 1; });

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (pagination.value && currentPage.value < pagination.value.last_page) {
    currentPage.value++;
  }
};

// --- Bulk Action Logic ---
type BulkAction = 'mark_read' | 'mark_unread' | 'delete';

const performBulkAction = async (action: BulkAction) => {
  if (selectedIds.value.length === 0) return;

  // Use custom confirmation modal for delete action
  if (action === 'delete') {
    confirmModalMessage.value = `Are you sure you want to delete ${selectedIds.value.length} selected message(s)? This action cannot be undone.`;
    // Set the callback function to execute if confirmed
    confirmActionCallback.value = () => executeBulkAction(action);
    isConfirmModalOpen.value = true; // Open the modal
    return; // Stop here, wait for modal confirmation
  }

  // For other actions, execute directly
  await executeBulkAction(action);
};

// Separate function to contain the actual fetch logic for bulk actions
const executeBulkAction = async (action: BulkAction) => {
  bulkActionLoading.value = true;
  bulkActionError.value = null;

  try {
    const data = await admin.messages.bulkAction({
      action: action,
      ids: selectedIds.value
    });

    if (!data.success) {
      throw new Error(data.message || `Failed to perform action: ${action}`);
    }

    // Success: Clear selection and refresh the message list
    toast.add({
      title: 'Success',
      description: data.message || `Action '${action}' completed successfully.`,
      color: 'success'
    });
    selectedIds.value = [];
    await props.refresh();

  } catch (err: unknown) {
     const errorMsg = err instanceof Error ? err.message : 'An unexpected error occurred during bulk action.';
     console.error(`Error performing bulk action (${action}):`, err);
     toast.add({
       title: 'Error',
       description: errorMsg,
       color: 'error'
     });
     bulkActionError.value = errorMsg; // Optionally show inline error
  } finally {
    bulkActionLoading.value = false;
  }
};

// --- View Message Logic ---
const viewMessage = async (message: Message) => {
  try {
    // Fetch the message from API to mark it as read on the backend
    const { data: freshMessage } = await admin.messages.get(message.id, {
      lazy: false,
      server: false,
    });

    // Extract the message data from the API response
    // The backend returns { success: true, data: Message }
    const messageData = freshMessage.value?.data || message;
    messageToView.value = messageData;
    isModalOpen.value = true;

    // Update the message in our local lists to reflect the read status
    const messageInAllMessages = allMessages.value.find(m => m.id === message.id);
    if (messageInAllMessages) {
      messageInAllMessages.is_read = true;
    }

    // Also update in the current page's messages
    const messageInCurrentPage = paginatedMessages.value.find((m: Message) => m.id === message.id);
    if (messageInCurrentPage) {
      messageInCurrentPage.is_read = true;
    }

  } catch (error) {
    console.error('Error fetching message for viewing:', error);

    // Fallback: Use the existing message data and show modal anyway
    messageToView.value = message;
    isModalOpen.value = true;

    // Still optimistically mark as read in the UI
    const messageInList = paginatedMessages.value.find((m: Message) => m.id === message.id);
    if (messageInList && !messageInList.is_read) {
      messageInList.is_read = true;
    }

    // Show error toast but don't prevent the modal from opening
    toast.add({
      title: 'Warning',
      description: 'Could not update read status on server, but message will still be displayed.',
      color: 'warning'
    });
  }
};

</script>

<style scoped>
/* Add specific table styles if needed */
.message-table-container {
  /* Add styles for the overall container */
}
.controls, .pagination-controls {
  /* Styles for control areas */
}
</style>
