<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

const email = ref('')
const password = ref('')
const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  const success = await authStore.login({ email: email.value, password: password.value })
  if (success) {
    router.push('/admin')
  }
}
</script>

<template>
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <form class="space-y-6" @submit.prevent="handleLogin">
        <BaseInput
          v-model="email"
          label="Email address"
          type="email"
          placeholder="admin@example.com"
          required
        />

        <BaseInput
          v-model="password"
          label="Password"
          type="password"
          required
        />

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-slate-600 hover:text-slate-500">Forgot your password?</a>
          </div>
        </div>

        <div>
          <BaseButton type="submit" block :loading="authStore.loading">
            Sign in
          </BaseButton>
        </div>
        
        <p v-if="authStore.error" class="text-red-500 text-sm text-center">{{ authStore.error }}</p>
      </form>
    </div>
  </div>
</template>
