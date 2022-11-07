<script>
  import { Firework } from 'svelte-loading-spinners'

  export let visible=true;
  export let small=false;
  let size=50;
$: {
  if(small) { size=30; }
  else { size=50; }
}
</script>

{#if visible}
  <center><div class="loading"><Firework size={size} color="blue" unit="px" duration="0.5s"></Firework></div></center>
{/if}
