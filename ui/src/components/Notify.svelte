<script>
  import { Button, Snackbar } from 'svelte-mui/src';

  export let visible = false;
  export let alert=false;
  export let bg="rgb(5, 139, 168)";
  
 $: {
 	if(alert) { bg="red"; }
 }
</script>

<Snackbar bind:visible {bg} bottom timeout=4>
    <slot />
    <span slot="action">
        <Button color="#ff0" on:click={() => (visible = false)}>Zavřít</Button>
    </span>
</Snackbar>

