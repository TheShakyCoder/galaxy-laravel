<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import CommandLayout from '@/Layouts/CommandLayout.vue';
import CommandIcon from '@/Components/CommandIcon.vue';

const props = defineProps({
    canPlay: { type: Boolean, required: true },
    servers: { type: Array, required: true },
});
const page = usePage();
const pilotCount = computed(
    () =>
        props.servers.filter((server) => server.reachable && server.summary)
            .length,
);
const openCount = computed(
    () => props.servers.filter((server) => server.is_open).length,
);
const play = () => router.visit(route('play'));
const formatNumber = (value) =>
    typeof value === 'number' && Number.isFinite(value)
        ? value.toLocaleString()
        : '—';
const formatDate = (value) => {
    const date = value ? new Date(value) : null;
    return date && !Number.isNaN(date.getTime()) ? date.toLocaleString() : '—';
};
const factions = new Map([
    ['the accord', 'accord'],
    ['accord', 'accord'],
    ['the swarm', 'swarm'],
    ['swarm', 'swarm'],
]);
const faction = (name) => factions.get(name?.toLowerCase()) ?? null;
</script>

<template>
    <CommandLayout title="Overview">
        <Head title="Pilot Command" />
        <div class="command-heading">
            <div>
                <p class="command-eyebrow">YOUR GALAXY, AT A GLANCE</p>
                <h1>
                    Welcome back,
                    <span>{{ page.props.auth.user.name }}.</span>
                </h1>
                <p class="command-muted mt-2 text-sm">
                    Your pilots, your fleet, your next destination.
                </p>
            </div>
            <span class="command-heading-mark" aria-hidden="true">
                <CommandIcon name="orbit" class="size-7" />
            </span>
        </div>
        <section class="command-hero" aria-labelledby="launch-title">
            <div class="command-starfield" aria-hidden="true" />
            <div class="command-hero-copy">
                <span
                    :class="[
                        'command-status',
                        canPlay ? 'is-ready' : 'is-neutral',
                    ]"
                >
                    <span class="command-status-dot" />
                    {{ canPlay ? 'READY FOR LAUNCH' : 'LAUNCH UNAVAILABLE' }}
                </span>
                <h2 id="launch-title">
                    The galaxy is
                    <br />
                    yours to explore
                    <span>.</span>
                </h2>
                <p>
                    {{
                        canPlay
                            ? 'Return to your fleet. Choose a server and pick up your story among the stars.'
                            : 'No game servers are open right now. Your next flight will be here when they reopen.'
                    }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-5">
                    <button
                        type="button"
                        class="command-button"
                        :disabled="!canPlay"
                        @click="play"
                    >
                        Launch Galaxy
                        <CommandIcon name="arrow" class="size-4" />
                    </button>
                    <a href="#pilots" class="command-hero-link">
                        View your pilots
                        <span aria-hidden="true">↘</span>
                    </a>
                </div>
            </div>
            <div class="command-hero-art" aria-hidden="true">
                <div class="command-orbit" />
                <img
                    src="/images/command/sardine.webp"
                    alt=""
                    width="1100"
                    height="800"
                    fetchpriority="high"
                />
                <div class="command-ship-caption">
                    <span>ACCORD FLEET</span>
                    <strong>SARDINE</strong>
                    <span>PATROL / INTERCEPTOR</span>
                </div>
            </div>
        </section>
        <dl class="command-stats">
            <div>
                <span class="command-stat-icon">
                    <CommandIcon name="server" />
                </span>
                <dt>Servers joined</dt>
                <dd>
                    {{ servers.length.toLocaleString() }}
                    <small>Your destinations</small>
                </dd>
            </div>
            <div>
                <span class="command-stat-icon">
                    <CommandIcon name="account" />
                </span>
                <dt>Pilot records</dt>
                <dd>
                    {{ pilotCount.toLocaleString() }}
                    <small>Currently available</small>
                </dd>
            </div>
            <div>
                <span class="command-stat-icon">
                    <CommandIcon name="launch" />
                </span>
                <dt>Open joined servers</dt>
                <dd>
                    {{ openCount.toLocaleString() }}
                    <small>Accepting launches</small>
                </dd>
            </div>
        </dl>
        <div class="command-content-grid">
            <section
                id="pilots"
                class="min-w-0 scroll-mt-6"
                aria-labelledby="pilots-title"
            >
                <div class="command-section-heading">
                    <div>
                        <p class="command-eyebrow">YOUR JOURNEY</p>
                        <h2 id="pilots-title">Pilot roster</h2>
                    </div>
                    <span class="command-count">
                        {{ servers.length }}
                        {{ servers.length === 1 ? 'server' : 'servers' }}
                    </span>
                </div>
                <div v-if="!servers.length" class="command-panel command-empty">
                    <CommandIcon name="orbit" class="mx-auto mb-5 size-10" />
                    <h3>Your story starts here.</h3>
                    <p>
                        Join a server to create your first pilot. Your faction,
                        ship and progress will appear here after you play.
                    </p>
                    <button
                        type="button"
                        class="command-button mt-6"
                        :disabled="!canPlay"
                        @click="play"
                    >
                        {{
                            canPlay
                                ? 'Choose your first server'
                                : 'Waiting for an open server'
                        }}
                        <CommandIcon name="arrow" class="size-4" />
                    </button>
                </div>
                <div v-else class="space-y-4">
                    <article
                        v-for="server in servers"
                        :key="server.slug"
                        class="command-panel command-pilot"
                        :data-faction="
                            server.reachable
                                ? faction(server.summary?.faction_name)
                                : null
                        "
                    >
                        <header class="command-pilot-header">
                            <h3>
                                <CommandIcon
                                    name="server"
                                    class="size-4 shrink-0"
                                />
                                {{ server.name }}
                            </h3>
                            <span
                                :class="[
                                    'command-server-state',
                                    server.is_open ? 'is-open' : 'is-closed',
                                ]"
                            >
                                <span class="command-status-dot" />
                                {{ server.is_open ? 'Open' : 'Closed' }}
                            </span>
                        </header>
                        <div
                            v-if="!server.reachable"
                            class="command-pilot-notice"
                        >
                            <CommandIcon
                                name="server"
                                class="size-7 shrink-0"
                            />
                            <div>
                                <h4>Pilot data unavailable</h4>
                                <p>
                                    This server can't be reached right now.
                                    Check back later to see your pilot.
                                </p>
                            </div>
                        </div>
                        <div
                            v-else-if="!server.summary"
                            class="command-pilot-notice"
                        >
                            <CommandIcon
                                name="account"
                                class="size-7 shrink-0"
                            />
                            <div>
                                <h4>A new journey awaits</h4>
                                <p>No pilot on this server yet.</p>
                            </div>
                        </div>
                        <template v-else>
                            <div class="command-pilot-body">
                                <div class="command-pilot-faction">
                                    <img
                                        v-if="
                                            faction(server.summary.faction_name)
                                        "
                                        :src="`/images/command/${faction(server.summary.faction_name)}.webp`"
                                        alt=""
                                        width="80"
                                        height="80"
                                        loading="lazy"
                                    />
                                    <span
                                        v-else
                                        class="command-unknown-faction"
                                    >
                                        <CommandIcon
                                            name="shield"
                                            class="size-8"
                                        />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="command-eyebrow">
                                            {{
                                                server.summary.faction_name ??
                                                'Faction not chosen'
                                            }}
                                        </p>
                                        <h4>
                                            {{
                                                server.summary.rank ??
                                                'Rank unavailable'
                                            }}
                                        </h4>
                                        <p class="command-muted mt-1 text-xs">
                                            <template
                                                v-if="
                                                    server.summary.level != null
                                                "
                                            >
                                                Level
                                                {{ server.summary.level }}
                                                <span aria-hidden="true">
                                                    ·
                                                </span>
                                            </template>
                                            {{
                                                formatNumber(server.summary.xp)
                                            }}
                                            XP
                                        </p>
                                    </div>
                                </div>
                                <dl class="command-flight-details">
                                    <div>
                                        <dt>
                                            <CommandIcon
                                                name="launch"
                                                class="size-3.5"
                                            />
                                            Assigned ship
                                        </dt>
                                        <dd>
                                            {{
                                                server.summary.ship_name ?? '—'
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>
                                            <CommandIcon
                                                name="pin"
                                                class="size-3.5"
                                            />
                                            Current system
                                        </dt>
                                        <dd>
                                            {{
                                                server.summary.system_name ??
                                                '—'
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="command-pilot-bottom">
                                <dl class="command-balances">
                                    <div>
                                        <dt>Scrip</dt>
                                        <dd>
                                            {{
                                                formatNumber(
                                                    server.summary.scrip,
                                                )
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Hydrogen</dt>
                                        <dd>
                                            {{
                                                formatNumber(
                                                    server.summary.hydrogen,
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                                <p class="command-last-played">
                                    Last played
                                    <time
                                        :datetime="
                                            server.last_played_at || undefined
                                        "
                                    >
                                        {{ formatDate(server.last_played_at) }}
                                    </time>
                                </p>
                            </div>
                        </template>
                    </article>
                </div>
                <p class="command-roster-note">
                    <CommandIcon name="shield" class="size-4 shrink-0" />
                    Each server holds its own pilot, fleet and progress.
                </p>
            </section>
            <aside class="min-w-0" aria-labelledby="factions-title">
                <div class="command-section-heading">
                    <div>
                        <p class="command-eyebrow">TWO SIDES. ONE GALAXY.</p>
                        <h2 id="factions-title">The factions</h2>
                    </div>
                </div>
                <div class="command-factions">
                    <div class="command-faction accord">
                        <div>
                            <span class="command-faction-index">
                                01 / ACCORD
                            </span>
                            <h3>The Accord</h3>
                            <p>
                                From the depths.
                                <br />
                                Into the stars.
                            </p>
                        </div>
                        <img
                            src="/images/command/accord.webp"
                            alt="Accord fish insignia"
                            width="138"
                            height="138"
                            loading="lazy"
                        />
                    </div>
                    <div class="command-faction swarm">
                        <div>
                            <span class="command-faction-index">
                                02 / SWARM
                            </span>
                            <h3>The Swarm</h3>
                            <p>
                                Born to soar.
                                <br />
                                Built to conquer.
                            </p>
                        </div>
                        <img
                            src="/images/command/swarm.webp"
                            alt="Swarm bird insignia"
                            width="138"
                            height="138"
                            loading="lazy"
                        />
                    </div>
                </div>
                <div class="command-field-note">
                    <span class="command-eyebrow">FLIGHT NOTES</span>
                    <h3>A fresh frontier on every server.</h3>
                    <p>
                        Choose where to play from the launchpad. You can return
                        here to check your pilots and manage your account.
                    </p>
                </div>
            </aside>
        </div>
    </CommandLayout>
</template>
