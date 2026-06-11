<script lang="ts">
    import Overlay from "./Overlay.svelte";
    import {createEventDispatcher} from "svelte";
    const dispatch = createEventDispatcher();

    export let selected: string|null = null
    export let hidden: boolean
    const toggle = function () {
        hidden = !hidden
        dispatch('toggle')
    }
</script>

<div
    class="section-expand"
    on:click={toggle}
>
    <div>Help and Information</div>
    <div class="flex-space"></div>
    <div>
        <div class="status {!hidden ? 'expanded' : ''}">＋</div>
    </div>
</div>

<Overlay hidden={hidden} on:toggle={toggle}>
    <h2>Help & Information</h2>
    <div class="form-help collapsible-wrapper">
        <p>
            This bot posts to your Slack or Discord channel for any match event during the World Cup.
            <br>
            Events include
        </p>
        <ul>
            <li><strong>Match Starting</strong></li>
            <li><strong>Goal Scored</strong></li>
            <li><strong>Goal Disallowed</strong></li>
            <li>and <strong>Match Finishing</strong> events <i>(declaring the winner, or if the match were drawn)</i></li>
        </ul>

        <p>
            Using this service requires generating some data, and deciding on some configuration values for your use case
        </p>

        <ul>
            <li class="{selected === 'webhook' ? 'selected' : ''}">
                <strong>Webhook</strong> is an incoming webhook you'll need to generate for your <a
                href="https://api.slack.com/messaging/webhooks">Slack</a> or <a
                href="https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks">Discord</a> channel. <small>This
                allows the bot to post to your channel, and is used as a key to fetch your configuration later to update it. If
                you no longer want the bot to post to your channel, you can re-enter your webhook and DELETE the configuration,
                or simply re-generate the webhook URL from your service which will void the old webhook</small>
            </li>
            <li class="{selected === 'service' ? 'selected' : ''}">
                <strong>Service</strong> is either Slack or Discord, this should be selected automatically when you enter your
                webhook
            </li>
            <li class="{selected === 'emoji' ? 'selected' : ''}">
                <strong>Emoji</strong> should be a comma separated list of emoji which will be randomly selected to be used in
                any messages for their event. Emoji can either be the emoji character 🫡, or the string alias e.g. <i>custom_emoji</i>
            </li>
            <li class="{selected === 'teams' ? 'selected' : ''}">
                <strong>Teams</strong> should be the name of the person allocated to each team, this can either be
                <ul>
                    <li>a <strong>tagged identifier</strong>, these are detected by a prefixed @. For Slack this should look
                        something like @fbloggs and for Discord this will be their <a
                            href="https://www.businessinsider.com/guides/tech/discord-id?r=US&IR=T">Discord user ID</a> e.g.
                        @123456789
                    </li>
                    <li>or the person's <strong>full name</strong> such as Fred Bloggs</li>
                </ul>
            </li>
        </ul>
    </div>
</Overlay>

<style>
    .section-expand {
        cursor: help;
    }

    .form-help {
        padding: 0 1em;
    }
</style>
