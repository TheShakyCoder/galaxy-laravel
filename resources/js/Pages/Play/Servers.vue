<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CommandLayout from '@/Layouts/CommandLayout.vue';
import CommandIcon from '@/Components/CommandIcon.vue';

defineProps({ servers: { type: Array, required: true } });
const launching = ref(null);
const choose = (server) => {
    if (launching.value) return;
    launching.value = server.slug;
    router.post(
        route('play.select', server.slug),
        {},
        {
            onFinish: () => {
                launching.value = null;
            },
        },
    );
};
</script>

<template>
    <CommandLayout title="Launchpad" section="play">
        <Head title="Choose a server" />
        <div class="command-heading">
            <div>
                <p class="command-eyebrow">PREPARE FOR DEPARTURE</p>
                <h1>Choose your destination.</h1>
                <p class="command-muted mt-2 text-sm">
                    Select a server and take your place among the stars.
                </p>
            </div>
        </div>
        <section
            class="command-launch-list"
            aria-label="Available servers"
            :aria-busy="launching !== null"
        >
            <div v-if="!servers.length" class="command-panel command-empty">
                <CommandIcon name="server" class="mx-auto mb-5 size-10" />
                <h2>No open servers right now.</h2>
                <p>
                    Check back soon. Your pilots and progress will be waiting
                    for you.
                </p>
                <Link :href="route('dashboard')" class="command-button mt-6">
                    Back to overview
                    <CommandIcon name="arrow" class="size-4" />
                </Link>
            </div>
            <article
                v-for="(server, index) in servers"
                :key="server.slug"
                class="command-panel command-destination"
            >
                <span class="command-destination-number">
                    {{ String(index + 1).padStart(2, '0') }}
                </span>
                <div class="min-w-0 flex-1">
                    <span class="command-eyebrow">OPEN FOR LAUNCH</span>
                    <h2>{{ server.name }}</h2>
                </div>
                <button
                    type="button"
                    class="command-button"
                    :disabled="launching !== null"
                    @click="choose(server)"
                >
                    {{ launching === server.slug ? 'Launching…' : 'Play' }}
                    <CommandIcon name="arrow" class="size-4" />
                </button>
            </article>
        </section>
        <p class="command-roster-note">
            <CommandIcon name="shield" class="size-4 shrink-0" />
            Each server holds its own pilot, fleet and progress.
        </p>
    </CommandLayout>
</template>
