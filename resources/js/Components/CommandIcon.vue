<script setup>
defineProps({ name: { type: String, required: true } });
const paths = {
    orbit: 'M21 12a9 9 0 1 1-9-9M3 17 17 3m-8 18L21 9M15 3h6v6M8 12h4v4H8z',
    overview: 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
    launch: 'm4 16 4-4m0 0c1-5 5-8 12-8 0 7-3 11-8 12m-4-4 4 4m-4-4H4l-2 6 6-2m4 0v4l-6 2 2-6M15 8h1',
    account: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-2a8 8 0 0 1 16 0v2',
    arrow: 'M4 12h16m-6-6 6 6-6 6',
    logout: 'M9 4H4v16h5m5-13 5 5-5 5m-6-5h11',
    menu: 'M4 6h16M4 12h16M4 18h16',
    close: 'm6 6 12 12M6 18 18 6',
    server: 'M4 3h16v7H4zM4 14h16v7H4zM7 6.5h.01M7 17.5h.01M12 6.5h5M12 17.5h5',
    pin: 'M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0ZM14 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z',
    shield: 'm12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6l8-4Zm-4 10 3 3 5-6',
};
</script>

<template>
    <svg
        aria-hidden="true"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <path :d="paths[name] || paths.orbit" />
    </svg>
</template>
