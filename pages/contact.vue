<template>
	<div class="max-w-xl mx-auto p-4 my-12">
		<h2 class="text-2xl md:text-3xl font-bold mb-6 text-center">Contact</h2>
	
		<form class="space-y-4" @submit.prevent="handleSubmit">
			<input
				v-model="form.name"
				type="text"
				placeholder="Your Name"
				required
				class="form-input"
			>
			<input
				v-model="form.email"
				type="email"
				placeholder="Your Email"
				required
				class="form-input"
			>
			<input
				v-model="form.subject"
				type="text"
				placeholder="Subject"
				required
				class="form-input"
			>
			<textarea
				v-model="form.message"
				rows="4"
				placeholder="Your Message"
				required
				class="form-input"
			/>
	
			<button
				type="submit"
				class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600 transition disabled:opacity-50"
				:disabled="loading"
			>
				{{ loading ? 'Sending...' : 'Send Message' }}
			</button>
	
			<p v-if="responseMsg" :class="success ? 'text-green-600' : 'text-red-500'">
				{{ responseMsg }}
			</p>
		</form>
	</div>
</template>
  
<script setup lang="ts">
	import { ref } from 'vue'
	import { useRuntimeConfig } from '#app'
	
	const config = useRuntimeConfig()
	const apiUrl = config.public.backendUrl
	
	const form = ref({
		name: '',
		email: '',
		subject: '',
		message: ''
	})
	
	const loading = ref(false)
	const responseMsg = ref('')
	const success = ref(false)
	
	const handleSubmit = async () => {
		loading.value = true
		responseMsg.value = ''
	
		try {
			const res = await fetch(`${apiUrl}/contact`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(form.value)
			})
		
			const data = await res.json()
			success.value = data.success
			responseMsg.value = data.message
		
			if (data.success) {
				form.value = { name: '', email: '', subject: '', message: '' }
			}
		} catch {
			responseMsg.value = 'Something went wrong. Please try again.'
			success.value = false
		} finally {
			loading.value = false
		}
	}
</script>
	
<style scoped>
	.form-input {
		width: 100%;
		padding-left: 1rem;
		padding-right: 1rem;
		padding-top: 0.5rem;
		padding-bottom: 0.5rem;
		border-radius: 0.25rem;
		border-width: 1px;
		border-color: #d1d5db;
		background-color: #ffffff;
		color: #000000;
		outline: 2px solid transparent;
		outline-offset: 2px;
		transition: border-color 0.2s, background-color 0.2s, color 0.2s;
	}
	.dark .form-input {
		border-color: #4b5563;
		background-color: #27272a;
		color: #ffffff;
	}
	.form-input:focus {
		border-color: #3b82f6;
		box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
	}
</style>