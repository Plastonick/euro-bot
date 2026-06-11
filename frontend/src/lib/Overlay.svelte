<script lang="ts">
    export let hidden: boolean

    import { createEventDispatcher } from 'svelte';
    import { blur } from 'svelte/transition';

    const dispatch = createEventDispatcher();

    const toggle = () => {
        hidden = !hidden
        dispatch('toggle')
    }
    const handleKeyDown = (event) => {
        if (hidden) return
        if (event.key === 'Escape') {
            toggle()
        }
    }
</script>

<svelte:window on:keydown={handleKeyDown} />

{#if !hidden }
    <div class="overlay-wrapper" on:click|self={toggle} transition:blur>
        <div class="content">
            <slot/>
        </div>
    </div>
{/if}

<style>
    .overlay-wrapper {
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;

        background: rgba(0, 0, 0, 0.5);
        cursor: pointer;
    }

    .overlay-wrapper .content {
        position: absolute;
        width: 70vw;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        cursor: auto;
        border-radius: 0.5em;

        max-height: calc(100vh - 10rem);
        overflow-y: auto;
    }
</style>
