<script>
  import { onMount } from "svelte";
  import LoginDialog from '../dialog/LoginDialog.svelte';
  import { apiUser } from "../ConfigService.js";

  export let login=[];
  export let visibleDialog=false;
</script>

{#if $apiUser}
{#if !login}
    &nbsp;
{:else}
  <span class="login">
    {#if !login['isLogged']}
      <a on:click={() => (visibleDialog = true)}>Přihlásit se</a>
    {:else}
      <b>Přihlášen:</b> {login['user']}
    {/if}
  </span>
  <LoginDialog bind:login bind:visibleDialog />
{/if}
{/if}

<style>
a {cursor:pointer; text-decoration:underline;}
a:hover {cursor:pointer; text-decoration:none;}

</style>

