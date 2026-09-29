<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    servers: {
        type: Array,
        required: true,
    },
});

const choose = (server) => router.post(route('play.select', server.slug));
</script>

<template>
    <Head title="Choose a server" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Choose a server
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-4 sm:px-6 lg:px-8">
                <p
                    v-if="servers.length === 0"
                    class="bg-white p-6 text-gray-900 shadow-xs sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
                >
                    No game servers are open right now.
                </p>
                <div
                    v-for="server in servers"
                    :key="server.slug"
                    class="flex items-center justify-between bg-white p-6 shadow-xs sm:rounded-lg dark:bg-gray-800"
                >
                    <span
                        class="text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        {{ server.name }}
                    </span>
                    <PrimaryButton @click="choose(server)">Play</PrimaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
