<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Sign in" />
    <v-app>
        <v-main class="d-flex align-center bg-grey-lighten-4">
            <v-container class="py-12" style="max-width: 460px">
                <v-card class="pa-6 pa-sm-9" elevation="2" rounded="lg">
                    <a href="/" class="d-block mb-8">
                        <img :src="'/images/fresh-food-logo.svg'" alt="Fresh Food" style="width: 170px">
                    </a>
                    <h1 class="text-h4 font-weight-bold mb-2">Welcome back</h1>
                    <p class="text-body-2 text-medium-emphasis mb-7">Sign in to manage your account.</p>

                    <form @submit.prevent="submit">
                        <v-text-field
                            v-model="form.email"
                            label="Email"
                            type="email"
                            autocomplete="username"
                            variant="outlined"
                            :error-messages="form.errors.email"
                        />
                        <v-text-field
                            v-model="form.password"
                            label="Password"
                            type="password"
                            autocomplete="current-password"
                            variant="outlined"
                            :error-messages="form.errors.password"
                        />
                        <v-checkbox v-model="form.remember" label="Remember me" density="compact" />
                        <v-btn type="submit" color="primary" size="large" block :loading="form.processing">Sign in</v-btn>
                    </form>

                    <p class="mt-5 text-caption text-medium-emphasis">Account access is managed by the store administrator.</p>
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>
