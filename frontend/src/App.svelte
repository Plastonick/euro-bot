<script lang="ts">
    import Github from './lib/Github.svelte'
    import Help from './lib/Help.svelte'
    import axios from 'axios'
    import { onMount } from 'svelte'

    const sweepstakesApi = import.meta.env.VITE_SWEEPSTAKES_API
    type Team = {
        id: number,
        name: string,
        tla: string,
        flag: string
    }
    type Feedback = { type: string, message: string }
    type FormValues = {
        webhook: string,
        service: string,
        owners: Record<string, string>,
        win: string,
        score: string,
        kickOff: string,
        draw: string,
        delaySeconds: number
    }

    const fetchAndHydrateConfiguration = async function () {
        if (!formValues.webhook) {
            return
        }

        const hydrateConfiguration = (result) => {
            formValues.service = result.data.service
            formValues.owners = result.data.owners
            formValues.win = result.data.win
            formValues.score = result.data.score
            formValues.kickOff = result.data.kickOff
            formValues.draw = result.data.draw
            formValues.delaySeconds = result.data.delaySeconds
            fetchFeedback = {type: 'success', message: 'Retrieved existing configuration'}
        }

        axios.get(`${sweepstakesApi}/configuration`, {params: {url: formValues.webhook}})
            .then(hydrateConfiguration)
            .catch((result) => {
                fetchFeedback = {type: 'error', message: result.response.data.message}
            })
    }

    const hydrateService = () => {
        const isSlack = formValues.webhook.indexOf('hooks.slack.com') !== -1
        const isDiscord = formValues.webhook.indexOf('discord.com') !== -1

        if (isSlack) {
            formValues.service = 'slack'
        } else if (isDiscord) {
            formValues.service = 'discord'
        }
    }

    const testConfiguration = async function () {
        if (!formValues.webhook) {
            persistFeedback = {type: 'error', message: 'No webhook URL detected'}

            return
        }

        if (formValues.service !== 'slack' && formValues.service !== 'discord') {
            persistFeedback = {type: 'error', message: 'No valid Service detected'}

            return
        }

        await axios.post(`${sweepstakesApi}/webhook-test`, formValues)
            .then((result) => {
                persistFeedback = {type: 'success', message: result.data.message}
            })
            .catch((result) => {
                persistFeedback = {type: 'error', message: result.response.data.message}
            })
    }

    const updateConfiguration = async function () {
        if (!formValues.webhook) {
            return
        }

        await axios.put(`${sweepstakesApi}/configuration`, buildConfigurationPayload())
            .then((result) => {
                persistFeedback = {type: 'success', message: result.data.message}
            })
            .catch((result) => {
                persistFeedback = {type: 'error', message: result.response.data.message}
            })
    }

    const deleteConfiguration = async function () {
        if (!formValues.webhook) {
            return
        }

        await axios.delete(`${sweepstakesApi}/configuration`, {params: {url: formValues.webhook}})
            .then((result) => {
                persistFeedback = {type: 'success', message: result.data.message}
            })
            .catch((result) => {
                persistFeedback = {type: 'error', message: result.response.data.message}
            })
    }

    const buildConfigurationPayload = function (): FormValues {
        const currentOwners = teams.reduce((owners, team) => {
            owners[team.tla] = formValues.owners[team.tla] ?? ''
            return owners
        }, {} as Record<string, string>)

        return {
            ...formValues,
            owners: currentOwners
        }
    }

    const fetchTeams = async function () {
        teamsLoading = true
        teamsFeedback = null

        await axios.get(`${sweepstakesApi}/teams`)
            .then((result) => {
                teams = result.data.teams
            })
            .catch((result) => {
                const message = result.response?.data?.message ?? 'Could not retrieve teams'
                teamsFeedback = {type: 'error', message}
            })
            .finally(() => {
                teamsLoading = false
            })
    }

    const formValues: FormValues = {
        webhook: '',
        service: '',
        owners: {},
        win: '🎉,🎊',
        score: '⚽️,💥',
        kickOff: '🏁,🚦',
        draw: '😐,🫠',
        delaySeconds: 120
    }

    let teams: Team[] = []
    let teamsLoading = true
    let teamsFeedback: Feedback | null = null
    let fetchFeedback: Feedback | null = null
    let persistFeedback: Feedback | null = null

    onMount(() => {
        fetchTeams()
    })

    $: if (fetchFeedback) {
        window.setTimeout(() => {
            fetchFeedback = null
        }, 5000)
    }

    $: if (persistFeedback) {
        window.setTimeout(() => {
            persistFeedback = null
        }, 5000)
    }

    let selected: string|null = null
    let helpHidden: boolean = true
    const toggleHelp = (event) => {
        if (helpHidden) {
            selected = event.target.dataset.helpType
        } else {
            selected = null
        }

        helpHidden = !helpHidden
    }

    let teamsExpanded = true
    const toggleTeams = function () {
        teamsExpanded = !teamsExpanded
    }
</script>


<Github/>

<div class="content">
  <h1>
    Euro 2024 Sweepstakes Announcer
  </h1>

  <h5>
    Add an announcer for match events to your Slack or Discord Euros sweepstakes channel
  </h5>

  <Help hidden={helpHidden} selected={selected} on:toggle={toggleHelp}/>

  <form on:submit|preventDefault>
    <div class="form-section">
      <span class="form-element">
        <label for="webhook" class="label-help" data-help-type="webhook" on:click={toggleHelp}>webhook</label>
        <input
            id="webhook"
            name="webhook"
            placeholder="https://hooks.slack.com/webhooks/..."
            on:change={hydrateService}
            bind:value={formValues.webhook}
        />
        <a
            class="button"
            on:click={fetchAndHydrateConfiguration}
            title="Fetch existing webhook configuration"
        >⬇</a>
        {#if fetchFeedback !== null}
          <small
              class="feedback {fetchFeedback.type}"
          >{fetchFeedback.message}</small>
        {/if}
      </span>
      <span class="form-element">
      <label for="service" class="label-help" data-help-type="service" on:click={toggleHelp}>Service</label>
      <select
          id="service"
          name="service"
          bind:value={formValues.service}
      >
        <option value="discord">Discord</option>
        <option value="slack">Slack</option>
      </select>
      </span>
    </div>

    <div class="form-section">
      <span class="form-element">
      <label for="win_emoji" class="label-help" data-help-type="emoji" on:click={toggleHelp}>win emoji</label>
      <input
          id="win_emoji"
          name="win_emoji"
          bind:value={formValues.win}
      />
      </span>

      <span class="form-element">
      <label for="score_emoji" class="label-help" data-help-type="emoji" on:click={toggleHelp}>score emoji</label>
      <input
          id="score_emoji"
          name="score_emoji"
          bind:value={formValues.score}
      />
      </span>

      <span class="form-element">
      <label for="draw_emoji" class="label-help" data-help-type="emoji" on:click={toggleHelp}>draw emoji</label>
      <input
          id="draw_emoji"
          name="draw_emoji"
          bind:value={formValues.draw}
      />
      </span>

      <span class="form-element">
      <label for="kickoff_emoji" class="label-help" data-help-type="emoji" on:click={toggleHelp}>kickoff emoji</label>
      <input
          id="kickoff_emoji"
          name="kickoff_emoji"
          bind:value={formValues.kickOff}
      />
      </span>

      <span class="form-element">
      <label for="delay_seconds" title="Delay to post messages in seconds">announcer delay</label>
      <input
          id="delay_seconds"
          name="delay_seconds"
          type="number"
          bind:value={formValues.delaySeconds}
      />
      </span>
    </div>

    <div
        class="section-expand"
        on:click={toggleTeams}
    >
      <div>Team Selection</div>
      <div class="flex-space"></div>
      <div>
        <div class="status {teamsExpanded ? 'expanded' : ''}">＋</div>
      </div>
    </div>

    <div class="collapsible-wrapper {teamsExpanded ? '' : 'collapsed'}">
      {#if teamsLoading}
        <small class="team-status">Loading teams...</small>
      {:else if teamsFeedback !== null}
        <small class="feedback {teamsFeedback.type}">{teamsFeedback.message}</small>
        <button type="button" on:click={fetchTeams}>retry</button>
      {:else if teams.length === 0}
        <small class="team-status">No teams returned by the API.</small>
      {:else}
        <div class="form-section">
          {#each teams as team (team.tla)}
          <span class="form-element">
          <label
              for="team_{team.tla}"
              class="label-help"
              data-help-type="teams"
              on:click={toggleHelp}
          >
            {team.flag} {team.name}
          </label>
          <input
              id="team_{team.tla}"
              name="team_{team.tla}"
              bind:value={formValues.owners[team.tla]}
          />
          </span>
          {/each}
        </div>
      {/if}
    </div>

    <div class="submit-section">
      <button type="submit" on:click={updateConfiguration}>update</button>
      <button class="test" title="Sends a test message to verify your webhook URL" on:click={testConfiguration}>test
      </button>
      <button class="delete" on:click={deleteConfiguration}>delete</button>
    </div>

    {#if persistFeedback !== null}
      <small
          class="feedback {persistFeedback.type}"
      >
        {persistFeedback.message}
      </small>
    {/if}
  </form>
</div>

<style>
    div.content {
        width: 65vw;
        max-width: 50em;
    }

    label.label-help {
        cursor: help;
    }

    .team-status {
        display: block;
        padding: 1em;
        text-align: left;
    }
</style>
