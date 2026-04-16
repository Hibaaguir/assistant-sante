<template>
    <div class="user-icon-container">
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="user-icon"
        >
            <circle cx="12" cy="8" r="4" />
            <path d="M6 20a6 6 0 0 1 12 0" />
        </svg>
    </div>
</template>

<style scoped>
.user-icon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background-color: #bacef5;
    border-radius: 50%;
}

.user-icon {
    width: 20px;
    height: 20px;
    color: #3b3b3d;
}
</style>
