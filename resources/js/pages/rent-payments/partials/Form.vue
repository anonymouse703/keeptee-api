<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { CreditCard, X } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import LeaseSelect from '@/components/ui/input/LeaseSelect.vue'
import BaseSelect from '@/components/ui/input/Select.vue'
import TextArea from '@/components/ui/input/TextArea.vue'

const props = defineProps<{
  rentalPayment?: any,
  status?: any,
  payment_method?: any
}>()

const formatDateForInput = (dateString: string | null): string | null => {
  if (!dateString) return null

  try {
    const date = new Date(dateString)

    if (isNaN(date.getTime())) return null

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
  } catch (error) {
    console.error('Error formatting date:', error)
    return null
  }
}

const selectedLease = ref<{ value: number; label: string } | null>(
  props.rentalPayment?.lease
    ? { value: props.rentalPayment.lease_id, label: props.rentalPayment.lease.title }
    : null
)

const originalLease = ref<{ value: number; label: string } | null>(
  selectedLease.value ? { ...selectedLease.value } : null
)

const form = useForm({
  lease_id: props.rentalPayment?.lease_id ?? null, 
  amount: props.rentalPayment?.amount ?? null,
  due_date: formatDateForInput(props.rentalPayment?.due_date) ?? null,
  paid_at: formatDateForInput(props.rentalPayment?.paid_at) ?? null,
  status: props.rentalPayment?.status ?? null,
  payment_method: props.rentalPayment?.payment_method ?? null,
  late_fee: props.rentalPayment?.late_fee ?? null,
  interest_rate: props.rentalPayment?.interest_rate ?? null,
  notes: props.rentalPayment?.notes ?? '',
})


const statusOptions = computed(() =>
  props.status?.map((opt: { key: string; label: string }) => ({
    label: opt.label,
    value: opt.key
  })) ?? []
)

const paymentMethodOptions = computed(() =>
  props.payment_method?.map((opt: { key: string; label: string }) => ({
    label: opt.label,
    value: opt.key
  })) ?? []
)

const allErrors = computed(() => form.errors)

const showAsyncSelect = ref(!props.rentalPayment?.lease)

const fetchLeases = async (query: string) => {
  try {
    const res = await fetch(`/leases/search-tenant?query=${encodeURIComponent(query)}`)
    if (!res.ok) throw new Error('Failed to fetch leases')
    const data = await res.json()
    return data.map((p: any) => ({ value: p.id, label: p.title }))
  } catch (error) {
    console.error('Error fetching leases:', error)
    return []
  }
}

const handleClearLease = () => {
  form.lease_id = null
  selectedLease.value = null
  showAsyncSelect.value = true
}

const handleCancelLeaseChange = () => {
  if (originalLease.value) {
    form.lease_id = originalLease.value.value
    selectedLease.value = { ...originalLease.value }
    showAsyncSelect.value = false
  }
}

const handleLeaseSelect = () => {
  if (form.lease_id && selectedLease.value) {
    originalLease.value = { ...selectedLease.value }
    showAsyncSelect.value = false
  }
}

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showAsyncSelect.value && originalLease.value) {
    event.preventDefault()
    handleCancelLeaseChange()
  }
}

// Submit form
const handleSubmit = () => {
  if (!form.lease_id && selectedLease.value) {
    form.lease_id = selectedLease.value.value
  }

  if (props.rentalPayment?.id) {
    form.put(`/rent-payments/${props.rentalPayment.id}`, { preserveScroll: true })
  } else {
    form.post('/rent-payments', { preserveScroll: true })
  }
}

// Cancel
const handleCancel = () => {
  form.reset()
  router.visit('/rent-payments')
}
</script>


<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <CreditCard class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ props.rentalPayment ? 'Edit Rental Payment' : 'Create New Rental Payment' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ props.rentalPayment
            ? 'Update rental payment information'
            : 'Add a new rental payment to manage your property' }}
        </p>
      </div>
    </div>

    <div class="space-y-5">

      <!-- Lease -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Property <span class="text-red-500">*</span>
        </label>

        <div v-if="!showAsyncSelect && selectedLease" class="relative">
          <input type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
            :value="selectedLease.label" readonly />

          <button type="button" @click="handleClearLease"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
            title="Clear selection">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

        <div v-else ref="asyncSelectContainer" class="relative" @keydown="handleKeyDown" tabindex="-1">
          <LeaseSelect v-model="form.lease_id" :fetchOptions="fetchLeases" :selectedOption="selectedLease"
            placeholder="Search and select a property..." :error="allErrors.lease_id"
            @update:modelValue="handleLeaseSelect" />


          <button v-if="originalLease" type="button" @click="handleCancelLeaseChange"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
            title="Cancel change (ESC)">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

      </div>

      <!-- Amount -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Amount <span class="text-red-500">*</span>
        </label>
        <BaseInput v-model="form.amount" type="number" placeholder="Enter payment amount" :error="allErrors.amount"
          class="w-full" />
      </div>

      <!-- Due Date -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Due Date <span class="text-red-500">*</span>
        </label>
        <BaseInput v-model="form.due_date" type="date" :error="allErrors.due_date" class="w-full" />
      </div>

      <!-- Paid At -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Paid At
        </label>
        <BaseInput v-model="form.paid_at" type="date" :error="allErrors.paid_at" class="w-full" />
      </div>

      <!-- Status -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Status
        </label>
        <BaseSelect v-model="form.status" :options="statusOptions" placeholder="Select status" :error="allErrors.status"
          class="w-full" />
        <p v-if="allErrors.status" class="text-red-500 text-sm mt-1">{{ allErrors.status }}</p>
      </div>

      <!-- Payment Method -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Payment Method
        </label>
        <BaseSelect v-model="form.payment_method" :options="paymentMethodOptions" placeholder="Select payment method"
          :error="allErrors.payment_method" class="w-full" />
        <p v-if="allErrors.payment_method" class="text-red-500 text-sm mt-1">{{ allErrors.payment_method }}</p>
      </div>


      <!-- Late Fee -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Late Fee
        </label>
        <BaseInput v-model="form.late_fee" type="number" placeholder="Enter late fee (if any)"
          :error="allErrors.late_fee" class="w-full" />
      </div>

      <!-- Interest Rate -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Interest Rate (%)
        </label>
        <BaseInput v-model="form.interest_rate" type="number" step="0.01" placeholder="Enter interest rate"
          :error="allErrors.interest_rate" class="w-full" />
      </div>

      <!-- Notes -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Notes
        </label>
        <TextArea v-model="form.notes" placeholder="Add any notes for this payment" :rows="4" id="notes" />
      </div>

    </div>


    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
        Cancel
      </BaseButton>

      <BaseButton type="button" @click="handleSubmit" :disabled="form.processing"
        class="bg-blue-600 text-white hover:bg-blue-700">
        {{ props.rentalPayment ? 'Update Rental Payment' : 'Create Rental Payment' }}
      </BaseButton>
    </div>
  </div>
</template>

<style scoped></style>