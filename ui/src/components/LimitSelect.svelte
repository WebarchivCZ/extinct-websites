<script>
  export let items = [15, 30, 50, 100, 300, 500, 1000];
  
  export let limit=100;

</script>

<select bind:value={limit}>
	{#each items as i}
		<option value={i}>{i} položek</option>
	{/each}
</select>

<style>
select {float:right; }
</style>
