<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    canPlay: {
        type: Boolean,
        required: true,
    },
    servers: {
        type: Array,
        required: true,
    },
    gameVersion: {
        type: String,
        required: true,
    },
});

const play = () => router.visit(route('play'));

const formatDate = (value) =>
    value ? new Date(value).toLocaleString() : '—';

const formatNumber = (value) =>
    typeof value === 'number' ? value.toLocaleString() : '—';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="flex items-center justify-between overflow-hidden bg-white p-6 shadow-xs sm:rounded-lg dark:bg-gray-800"
                >
                    <div>
                        <p class="text-gray-900 dark:text-gray-100">
                            <template v-if="canPlay">Ready for launch.</template>
                            <template v-else>
                                No game servers are open right now.
                            </template>
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Galaxy {{ gameVersion }}
                        </p>
                    </div>
                    <PrimaryButton :disabled="!canPlay" @click="play">
                        Play
                    </PrimaryButton>
                </div>

                <div
                    v-for="server in servers"
                    :key="server.slug"
                    class="overflow-hidden bg-white p-6 shadow-xs sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="flex items-baseline justify-between">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ server.name }}
                        </h3>
                        <span
                            v-if="!server.is_open"
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            Closed
                        </span>
                    </div>

                    <p
                        v-if="!server.reachable"
                        class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                    >
                        This server can't be reached right now.
                    </p>
                    <p
                        v-else-if="!server.summary"
                        class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                    >
                        No pilot on this server yet.
                    </p>
                    <dl
                        v-else
                        class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3"
                    >
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Rank
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ server.summary.rank ?? '—' }}
                                <span
                                    v-if="server.summary.level"
                                    class="text-gray-500 dark:text-gray-400"
                                >
                                    (level {{ server.summary.level }})
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                XP
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ formatNumber(server.summary.xp) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Faction
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ server.summary.faction_name ?? 'Not chosen' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Ship
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ server.summary.ship_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Location
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ server.summary.system_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Tope
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ formatNumber(server.summary.tope) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Hydrogen
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ formatNumber(server.summary.hydrogen) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                Last played
                            </dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ formatDate(server.last_played_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
