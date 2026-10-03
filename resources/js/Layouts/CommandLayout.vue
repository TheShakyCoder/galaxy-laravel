<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import CommandIcon from '@/Components/CommandIcon.vue';
import '../../css/command.css';

defineProps({
    title: { type: String, required: true },
    section: { type: String, default: 'dashboard' },
});
const page = usePage();
const navigationOpen = ref(false);
const menuButton = ref(null);
const user = computed(() => page.props.auth.user);
const initials = computed(() =>
    user.value.name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
const navigation = [
    { name: 'dashboard', label: 'Overview', icon: 'overview' },
    { name: 'play', label: 'Launchpad', icon: 'launch' },
    { name: 'profile.edit', label: 'Account', icon: 'account' },
];
const closeNavigation = () => {
    navigationOpen.value = false;
    menuButton.value?.focus();
};
watch(
    () => page.url,
    () => {
        navigationOpen.value = false;
    },
);
</script>

<template>
    <div class="command-theme" @keydown.esc="closeNavigation">
        <a class="command-skip" href="#command-main">Skip to content</a>
        <aside class="command-rail">
            <div class="flex items-center justify-between gap-4">
                <Link
                    :href="route('dashboard')"
                    class="command-brand"
                    aria-label="Galaxy dashboard"
                >
                    <CommandIcon name="orbit" class="size-9 text-teal-300" />
                    <span>
                        GALAXY
                        <small>PILOT COMMAND</small>
                    </span>
                </Link>
                <button
                    ref="menuButton"
                    type="button"
                    class="command-menu lg:hidden"
                    :aria-expanded="navigationOpen"
                    aria-controls="command-navigation"
                    :aria-label="
                        navigationOpen ? 'Close navigation' : 'Open navigation'
                    "
                    @click="navigationOpen = !navigationOpen"
                >
                    <CommandIcon
                        :name="navigationOpen ? 'close' : 'menu'"
                        class="size-5"
                    />
                </button>
            </div>
            <div
                id="command-navigation"
                :class="[
                    navigationOpen ? 'flex' : 'hidden',
                    'command-navigation lg:flex',
                ]"
            >
                <nav aria-label="Main navigation" class="space-y-2">
                    <p class="command-nav-caption">YOUR COMMAND</p>
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="route(item.name)"
                        :aria-current="
                            section === item.name ? 'page' : undefined
                        "
                        :class="[
                            'command-nav-link',
                            { 'is-active': section === item.name },
                        ]"
                    >
                        <CommandIcon
                            :name="item.icon"
                            class="size-5 shrink-0"
                        />
                        {{ item.label }}
                        <span
                            v-if="section === item.name"
                            class="ml-auto size-1.5 rounded-full bg-teal-300"
                        />
                    </Link>
                </nav>
                <div class="command-rail-bottom">
                    <div class="command-rail-note">
                        <CommandIcon
                            name="orbit"
                            class="mb-4 size-7 text-teal-400"
                        />
                        <p>
                            A galaxy worth
                            <br />
                            fighting for.
                        </p>
                        <span>One pilot. Your story.</span>
                    </div>
                    <div class="command-identity">
                        <span class="command-avatar">{{ initials }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">
                                {{ user.name }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Pilot account
                            </p>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="command-menu shrink-0"
                            aria-label="Log out"
                            title="Log out"
                        >
                            <CommandIcon name="logout" class="size-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </aside>
        <div class="command-workspace">
            <header class="command-topbar">
                <div class="flex min-w-0 items-center gap-3 text-sm">
                    <span class="command-muted hidden sm:inline">
                        Pilot Command
                    </span>
                    <span
                        aria-hidden="true"
                        class="command-muted hidden sm:inline"
                    >
                        /
                    </span>
                    <span class="font-semibold">{{ title }}</span>
                </div>
                <Link
                    :href="route('profile.edit')"
                    class="command-account-link"
                >
                    <span class="hidden max-w-48 truncate sm:block">
                        {{ user.name }}
                    </span>
                    <span class="command-avatar small">{{ initials }}</span>
                </Link>
            </header>
            <main id="command-main" tabindex="-1" class="command-main">
                <slot />
            </main>
            <footer class="command-footer">
                <span>
                    GALAXY
                    <span aria-hidden="true">/</span>
                    PILOT COMMAND
                </span>
                <span>Your next frontier awaits.</span>
            </footer>
        </div>
    </div>
</template>
